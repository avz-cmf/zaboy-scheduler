<?php

namespace zaboy\scheduler\Broker;

use Xiag\Rql\Parser\Node\SortNode;
use Xiag\Rql\Parser\Query;
use zaboy\rest\DataStore\Interfaces\DataStoresInterface;
use zaboy\scheduler\DataStore\UTCTime;
use zaboy\scheduler\FileSystem\Parser\ErrorParser;

class ScriptBroker
{
    protected $pidDataStore;

    /** @var ErrorParser $parser */
    protected $parser;

    /**
     * ScriptBroker constructor.
     *
     * @param DataStoresInterface $pidDataStore
     * @param ErrorParser $parser
     */
    public function __construct(DataStoresInterface $pidDataStore, ErrorParser $parser)
    {
        $this->pidDataStore = $pidDataStore;
        $this->parser = $parser;
    }

    public function setFileInfo($promiseId, $pId, $stdOutFilename, $stdErrFilename)
    {
        $itemData = [
            'promiseId' => $promiseId,
            'pId' => $pId,
            'startedAt' => UTCTime::getUTCTimestamp(),
            'timeout' => 30,
            'stdout' => $stdOutFilename,
            'stderr' => $stdErrFilename,
        ];
        $this->pidDataStore->create($itemData);
    }

    /**
     * Checks a status of processes.
     *
     * If the process finished, reads its log files and return the status of finishing and errors/output
     */
    public function checkProcess()
    {
        // TODO: проверить все процессы. Зависшие закрыть принудительно (reject), остальные обработать

        $sortNode = new SortNode(['startedAt' => +1]);

        $query = new Query();
        $query->setSort($sortNode);

        $rows = $this->pidDataStore->query($query);

        foreach ($rows as $row) {

            $pId = intval($row['pId']);
            // checks timeout
            $expireTime = floatval($row['startedAt']) + intval($row['timeout']);
            if ($expireTime <= UTCTime::getUTCTimestamp()) {
                $this->killProcess($pId);
            }

            // checks process existing
            if (!posix_kill($pId, 0)) {
                // The process is finished
                $this->postFinishProcess($row);
                $this->pidDataStore->delete($row['id']);
            }
        }
    }

    /**
     * Reads a content of log-files of specified process
     *
     * @param $row
     * @throws \Exception
     */
    public function postFinishProcess($row)
    {
        $errors = $this->parser->parseLog($row['stderr']);
        $output = $this->parser->parseLog($row['stdout']);
        if ($errors['fatalStatus']) {
            $this->reject($errors['message']);
        } else {
            $this->resolve($output['message'] . PHP_EOL . $errors['message']);
        }
    }

    /**
     * @param $output
     * @return mixed
     */
    public function resolve($output)
    {
        return $output;
    }

    /**
     * @param $reason
     * @return mixed
     */
    public function reject($reason)
    {
        return $reason;
    }

    /**
     * Kills the process
     *
     * @param $pId
     */
    protected function killProcess($pId)
    {
        posix_kill($pId, SIGKILL);
        usleep(1000);
    }
}
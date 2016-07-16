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

    /**
     * ScriptBroker constructor.
     *
     * @param DataStoresInterface $pidDataStore
     * @param ErrorParser $parser
     */
    public function __construct(DataStoresInterface $pidDataStore, ErrorParser $parser)
    {
        $this->pidDataStore = $pidDataStore;
    }

    public function setFileInfo($promiseId, $pId, $stdOutFilename, $stdErrFilename)
    {
        $itemData = [
            'promiseId' => $promiseId,
            'pid' => $pId,
            'startedAt' => UTCTime::getUTCTimestamp(),
            'timeout' => 30,
            'stdout' => $stdOutFilename,
            'stderr' => $stdErrFilename,
        ];
        $this->pidDataStore->create($itemData);
    }

    public function checkProcess()
    {
        // TODO: проверить все процессы. Зависшие закрыть принудительно (reject), остальные обработать

        $sortNode = new SortNode(['+startedAt']);

        $query = new Query();
        $query->setSort($sortNode);

        $rows = $this->pidDataStore->query($query);
        foreach ($rows as $row) {

            // checks process existing
            $pId = intval($row['pid']);
            if (!posix_kill($pId, 0)) {
                // The process is finished

            }


            // checks timeout
            $expireTime = floatval($row['startedAt']) + intval($row['timeout']);
            if ($expireTime <= UTCTime::getUTCTimestamp()) {
                // TODO: reject
            }

            // checks reject
        }
    }

    public function postFinishProcess($row)
    {

    }
}
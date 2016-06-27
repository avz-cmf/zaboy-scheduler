<?php

namespace zaboy\scheduler\Callback;

use zaboy\rest\DataStore\DataStoreAbstract;

/**
 * Class Worker
 *
 * This class implements an abstraction of callback - php-script which is run in background.
 *
 * The different with ScriptCallback is that this callback gets back the PID of run process and allows checking
 * of its working.
 *
 * <b>This callback type will not work in Windows</b>. It will work in POSIX-systems only.
 *
 * There are three variants of the running of the process:
 * 1. Using the function shell_exec:
 * <code>
 * $cmd = "php " . __DIR__ . DIRECTORY_SEPARATOR . "test1.php > /dev/null 2>&1 & echo $!";
 * $output = shell_exec($cmd);
 * $pId = intval($output);
 * </code>
 *
 * 2. Using the pair "nohup" and the function "exec":
 * <code>
 * $cmd = "nohup php " . __DIR__ . DIRECTORY_SEPARATOR . "test1.php > /dev/null 2>&1 & echo $!";
 * exec($cmd, $output);
 * $pId = $output[0];
 * </code>
 *
 * 3. Using the function "proc_open" and its satellites like "proc_get_status", "proc_close" etc:
 * <code>
 * $descriptorSpec = [
 *     0 => array("pipe", "r"),
 *     1 => array("pipe", "w"),
 *     2 => array("pipe", "w"),
 * ];
 * $cmd = "php " . __DIR__ . DIRECTORY_SEPARATOR . "test1.php > /dev/null 2>&1 & echo $!";
 * $process = proc_open($cmd, $descriptorSpec, $pipes);
 * $metaInfo = proc_get_status($process);
 * // The PID will be in the pipe of the stdout - in the second (index == 1)
 * $pId = intval(fgets($pipes[1]));
 *
 * foreach ($pipes as $pipe) {
 *     fclose($pipe);
 * }
 * proc_close($process);
 * </code>
 *
 * We decided to use the first variant because it's the fastest.
 *
 * In order to find out the status of the running process this callback uses the function "posix_kill":
 * <code>
 * return posix_kill($pId, 0);
 * </code>
 * If the process exists, "posix_kill" will return TRUE
 *
 * What about the Windows?
 *
 * The Windows allows to run a process in the background:
 * <code>
 * $cmd = "start /B php " . __DIR__ . DIRECTORY_SEPARATOR . "test1.php";
 * $output = shell_exec($cmd);
 * $pId = intval($output);
 * </code>
 *
 * But there is not exist any variant how to get the PID of the run process immediately.
 * There is one opportunity to get it - using the COM-Objects scripts. But we don't use it at all.
 *
 * In addition, in Windows don't work the functions "posix_*".
 *
 * And the method of getting the PID from TASKLIST is very difficult, slow and inaccurate.
 * The Windows runs all command starting at "php ..." as "php.exe" image name.
 * If a lot of scripts will be run which of them is our?
 *
 * @package zaboy\scheduler\Callback
 */
class Worker extends Script
{
    protected $startProcessMethod;

    /** @var  \zaboy\rest\DataStore\DataStoreAbstract */
    protected $dataStore;

    /**
     * {@inherit}
     *
     * {@inherit}
     */
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        if (!isset($params['dataStore']) || !$params['dataStore'] instanceof DataStoreAbstract) {
            throw new CallbackException("Expected necessary parameter \"dataStore\" type of DataStoreAbstract");
        }
        $this->dataStore = $params['dataStore'];

        $this->checkEnvironment();
    }

    /**
     * Checks an environment.
     *
     * If it the Windows or either of "shell_exec" or "posix_kill" absents it will not work.
     *
     * @throws CallbackException
     */
    private function checkEnvironment()
    {
        if ('Windows' == substr(php_uname(), 0, 7)) {
            throw new CallbackException("This callback type will not work in Windows");
        }
        if (!function_exists('shell_exec')) {
            throw new CallbackException("The function \"shell_exec\" does not exist or it is not allowed.");
        }
        if (!function_exists('posix_kill')) {
            throw new CallbackException("The function \"posix_kill\" does not exist or it is not allowed.");
        }
    }

    /**
     * {@inherit}
     *
     * {@inherit}
     */
    public function call(array $options = [])
    {
        $cmd = "php " . $this->script;
        $cmd .= Script::makeParamsString(['scriptOptions' => Script::encodeParams($options)]);
        $cmd .= "  > /dev/null 2>&1 & echo $!";

        $output = trim(shell_exec($cmd));
        if (!is_numeric($output)) {
            throw new CallbackException("The output of the script is ambiguous. Probably there is an error in the script");
        }
        $pId = intval($output);

//        $itemData = [
//            'pid' => $pId,
//            'startedAt' => UTCTime::getUTCTimestamp(),
//            'scriptName' => "Worker: ",
//            'timeout' => 30
//        ];
//        $this->dataStore->create($itemData);
        return $pId;
    }

    /**
     * Check the running status of the process
     * @param $pId
     * @return bool
     */
    public function isProcessWorking($pId)
    {
        return posix_kill($pId, 0);
    }
}
<?php

namespace zaboy\scheduler\Callback\Decorators;

use zaboy\scheduler\Broker\ScriptBroker;
use zaboy\scheduler\Callback\CallbackException;
use zaboy\scheduler\Callback\Script;

class ScriptDecorator extends Script
{
    const SCRIPT_NAME = 'scripts/scriptProxy.php';

    /**
     * @var array - the options from config for passing to the callback
     */
    protected $rpcCallback;


    /**
     * ScriptDecorator constructor.
     * @param $rpcCallback
     * @throws CallbackException
     */
    public function __construct($rpcCallback)
    {
        if (!is_file(self::SCRIPT_NAME)) {
            throw new CallbackException("The handler script \"scriptProxy.php\" does not exist in the folder \"script\"");
        }
        parent::__construct(self::SCRIPT_NAME, null);

        $this->rpcCallback = $rpcCallback;

        $this->checkEnvironment();
    }

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

    public function getPromiseId()
    {
        return uniqid();
    }

    /**
     * {@inherit}
     *
     * {@inherit}
     */
    public function call(array $options = [])
    {
        // Merge the options from config with passed options
        $options = array_merge(['rpc_callback' => $this->rpcCallback], $options);

        // Files names for stdout and stderr
        $stdOutFilename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('stdout_', 1);
        $stdErrFilename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('stderr_', 1);

        $cmd = $this->commandPrefix . ' ' . $this->script;
        $cmd .= Script::makeParamsString(['scriptOptions' => self::encodeParams($options)]);
        $cmd .= "  1>{$stdOutFilename} 2>{$stdErrFilename} & echo $!";

        $output = trim(shell_exec($cmd));
        if (!is_numeric($output)) {
            throw new CallbackException("The output of the script is ambiguous."
                . "Probably there is an error in the script");
        }
        $pId = intval($output);

        ScriptBroker::setFileInfo($this->getPromiseId(), $pId, $stdOutFilename, $stdErrFilename);
    }
}
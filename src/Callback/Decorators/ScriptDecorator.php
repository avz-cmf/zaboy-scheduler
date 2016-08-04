<?php

namespace zaboy\scheduler\Callback\Decorators;

use zaboy\scheduler\Broker\ScriptBroker;
use zaboy\scheduler\Callback\CallbackException;
use zaboy\scheduler\Callback\Decorators\Interfaces\AsyncDecoratorInterface;
use zaboy\scheduler\FileSystem\CommandLineWorker;
use zaboy\scheduler\FileSystem\ScriptWorker;
use zaboy\async\Promise\Store;
use zaboy\async\Promise\Promise;

class ScriptDecorator extends ScriptWorker implements AsyncDecoratorInterface
{
    const SCRIPT_NAME = 'scripts/scriptProxy.php';

    /**
     * @var array - the options from config for passing to the callback
     */
    protected $rpcCallback;

    /** @var ScriptBroker $scriptBroker */
    protected $scriptBroker;

    /** @var Store $store */
    protected $store;

    /** @var  CommandLineWorker $commandLineWorker */
    protected $commandLineWorker;

    /**
     * ScriptDecorator constructor.
     *
     * @param $rpcCallback
     * @param ScriptBroker $scriptBroker
     * @param CommandLineWorker $commandLineWorker
     * @param Store $store
     * @throws CallbackException
     */
    public function __construct($rpcCallback, ScriptBroker $scriptBroker,
        CommandLineWorker $commandLineWorker, Store $store)
    {
        if (!is_file(self::SCRIPT_NAME)) {
            throw new CallbackException('The handler script "scriptProxy.php" does not exist in the folder "script"');
        }
        parent::__construct(self::SCRIPT_NAME, null);

        $this->rpcCallback = $rpcCallback;

        $this->checkEnvironment();
        $this->commandLineWorker = $commandLineWorker;
        $this->store = $store;
        $this->scriptBroker = $scriptBroker;
    }

    /**
     * Checks an environment where this script was run
     *
     * It's not allowed to run in Windows
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
     * @return Promise
     */
    public function getPromise()
    {
        return new Promise($this->store);
    }

    /**
     * @param array $parameters
     * @return void|\zaboy\async\Promise\Interfaces\PromiseInterface
     * @throws CallbackException
     */
    public function asyncCall(array $parameters = [])
    {
        $promise = $this->getPromise();
        // Merge the options from config with passed options
        /** @var array|null $parameters */
        $options = array_merge(
            ['rpc_callback' => $this->rpcCallback, 'promise' => $promise->getPromiseId()],
            (array) $parameters
        );

        // Files names for stdout and stderr
        $stdOutFilename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('stdout_', 1);
        $stdErrFilename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('stderr_', 1);

        $cmd = $this->commandPrefix . ' ' . $this->script;
        $cmd .= $this->commandLineWorker->makeParamsString([
            'scriptOptions' => $this->commandLineWorker->encodeParams($options)
        ]);
        $cmd .= "  1>{$stdOutFilename} 2>{$stdErrFilename} & echo $!";
        $output = trim(shell_exec($cmd));
        if (!is_numeric($output)) {
            throw new CallbackException("The output of the script is ambiguous."
                . "Probably there is an error in the script");
        }
        $pId = intval($output);

        $this->scriptBroker->setFileInfo($promise->getPromiseId(), $pId, $stdOutFilename, $stdErrFilename);

        return $promise;
    }


}
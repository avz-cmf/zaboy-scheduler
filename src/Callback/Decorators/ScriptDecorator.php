<?php

namespace zaboy\scheduler\Callback\Decorators;

use zaboy\scheduler\Broker\ScriptBroker;
use zaboy\scheduler\Callback\CallbackException;
use zaboy\scheduler\Callback\Decorators\Interfaces\AsyncDecoratorInterface;
use zaboy\scheduler\FileSystem\CommandLineWorker;
use zaboy\scheduler\FileSystem\ScriptWorker;
use zaboy\async\Promise;

class ScriptDecorator extends ScriptWorker implements AsyncDecoratorInterface
{
    const SCRIPT_NAME = 'scripts/scriptProxy.php';

    /** @var ScriptBroker $scriptBroker */
    protected $scriptBroker;

    /** @var Promise\Broker $promiseBroker */
    protected $promiseBroker;

    /** @var string */
    protected $script;

    /**
     * ScriptDecorator constructor.
     *
     * @param ScriptBroker $scriptBroker
     * @throws CallbackException
     */
    public function __construct(ScriptBroker $scriptBroker)
    {
        parent::__construct(null, null);
//        $this->checkEnvironment();
        $this->scriptBroker = $scriptBroker;
        $this->promiseBroker = $scriptBroker->getPromiseBroker();
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
     * @param array $parameters
     * @return void|\zaboy\async\Promise\Interfaces\PromiseInterface
     * @throws CallbackException
     */
    public function asyncCall(array $parameters = [])
    {
        if (is_null($this->script)) {
            if (!is_file(self::SCRIPT_NAME)) {
                throw new CallbackException('The handler script "scriptProxy.php" does not exist in the folder "script"');
            }
            $this->script = self::SCRIPT_NAME;
        }

        $promise = $this->promiseBroker->make();
        // Merge the options from config with passed options
        /** @var array|null $parameters */
        $options = array_merge(
            ['promise' => $promise->getId()],
            $parameters
        );

        // Files names for stdout and stderr
        $stdOutFilename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('stdout_', 1);
        $stdErrFilename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('stderr_', 1);

        /** @var  CommandLineWorker $commandLineWorker */
        $commandLineWorker = new CommandLineWorker();
        $cmd = $this->commandPrefix . ' ' . $this->script;
        $cmd .= $commandLineWorker->makeParamsString([
            'scriptOptions' => $commandLineWorker->encodeParams($options)
        ]);
        $cmd .= "  1>{$stdOutFilename} 2>{$stdErrFilename} & echo $!";
        $output = trim(shell_exec($cmd));
//        if (!is_numeric($output)) {
//            throw new CallbackException("The output of the script is ambiguous."
//                . "Probably there is an error in the script");
//        }
//        $pId = intval($output);
//
//        $this->scriptBroker->setFileInfo($promise->getId(), $pId, $stdOutFilename, $stdErrFilename);

        return $promise;
    }
}
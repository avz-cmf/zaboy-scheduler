<?php

namespace zaboy\scheduler\Callback;

use zaboy\scheduler\Callback\CallbackException;
use zaboy\scheduler\Callback\Interfaces\CallbackInterface;
use zaboy\scheduler\FileSystem\CommandLineWorker;
use zaboy\scheduler\FileSystem\ScriptWorker;

/**
 * Class Callback\Script
 *
 * This class implements an abstraction of callback - php-script
 * It can parse parameters from command line
 *
 * @see \zaboy\scheduler\Callback\Factory\ScriptAbstractFactory
 * @package zaboy\scheduler\Callback
 */
class Script extends ScriptWorker implements CallbackInterface
{
    /** @var  CommandLineWorker $commandLineWorker */
    protected $commandLineWorker;

    /**
     * Script constructor.
     */
    public function __construct($scriptName, $commandPrefix, CommandLineWorker $commandLineWorker)
    {
        parent::__construct($scriptName, $commandPrefix);
        $this->commandLineWorker = $commandLineWorker;
    }


    /**
     * {@inherit}
     *
     * {@inherit}
     */
    public function call(array $options = [])
    {
        $cmd = $this->commandPrefix . ' ' . $this->script;
        $cmd .= $this->commandLineWorker->makeParamsString([
            'scriptOptions' => $this->commandLineWorker->encodeParams($options)
        ]);

        if (substr(php_uname(), 0, 7) == "Windows"){
            pclose(popen($cmd, "r"));
        }
        else {
            exec($cmd . " > /dev/null");
        }
//        exec($cmd, $output);
//        return $output;
    }
}
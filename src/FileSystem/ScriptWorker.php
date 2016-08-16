<?php

namespace zaboy\scheduler\FileSystem;

use zaboy\scheduler\Callback\CallbackException;

class ScriptWorker
{
    const DEFAULT_COMMAND_PREFIX = 'php';

    protected $script = null;

    protected $commandPrefix = self::DEFAULT_COMMAND_PREFIX;

    /**
     * Script constructor.
     *
     * @param $scriptName
     * @param string $commandPrefix
     * @throws CallbackException
     */
    public function __construct($scriptName, $commandPrefix)
    {
        if ($scriptName) {
            $this->setScript($scriptName);
        }
        if ($commandPrefix) {
            $this->commandPrefix = $commandPrefix;
        }
    }


    public function setScript($scriptName)
    {
        if (is_file($scriptName)) {
            $this->script = $scriptName;
        } else {
            $filename = getcwd() . DIRECTORY_SEPARATOR . $scriptName;
            if (!is_file($filename)) {
                throw new CallbackException("Specified script \"{$scriptName}\" does not exist in path \""
                    . getcwd() . "\"");
            }
            $this->script = $filename;
        }
    }
}
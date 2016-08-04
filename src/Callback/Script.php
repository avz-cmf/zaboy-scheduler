<?php

namespace zaboy\scheduler\Callback;

use zaboy\scheduler\Callback\Interfaces\CallbackInterface;
use zaboy\scheduler\FileSystem\CommandLineWorker;
use zaboy\scheduler\FileSystem\Parser\OutputParser;
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

    /** @var  OutputParser $parser */
    protected $parser;

    /**
     * Script constructor.
     */
    public function __construct($scriptName, $commandPrefix, CommandLineWorker $commandLineWorker, OutputParser $parser)
    {
        parent::__construct($scriptName, $commandPrefix);
        $this->commandLineWorker = $commandLineWorker;
        $this->parser = $parser;
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

        // Files names for stdout and stderr
        $stdOutFilename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('stdout_', 1);
        $stdErrFilename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('stderr_', 1);
        $cmd .= "  1>{$stdOutFilename} 2>{$stdErrFilename}";

        if (substr(php_uname(), 0, 7) == "Windows") {
            pclose(popen($cmd, "r"));
        } else {
            $cmd .= " & echo $!";
            exec($cmd, $output);
        }
        $errors = $this->parser->parseFile($stdErrFilename);
        $output = $this->parser->parseFile($stdOutFilename);
        if ($errors['fatalStatus']) {
            throw new CallbackException($errors['message']);
        }
        return $output['message'];
    }
}
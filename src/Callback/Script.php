<?php

namespace zaboy\scheduler\Callback;

use zaboy\scheduler\Callback\Interfaces\CallbackInterface;

/**
 * Class Callback\Script
 *
 * This class implements an abstraction of callback - php-script
 * It can parse parameters from command line
 *
 * @see \zaboy\scheduler\Callback\Factory\ScriptAbstractFactory
 * @package zaboy\scheduler\Callback
 */
class Script implements CallbackInterface
{
    const PARAMETERS_PREFIX = '-';

    protected $script = null;

    /**
     * {@inherit}
     *
     * {@inherit}
     */
    public function __construct(array $params = [])
    {
        if (!isset($params['script_name'])) {
            throw new CallbackException("The necessary parameter \"script_name\" is expected");
        }

        $script = $params['script_name'];
        unset($params['script_name']);
        if (is_file($script)) {
            $this->script = $script;
        } else {
            $filename = getcwd() . DIRECTORY_SEPARATOR . $script;
            if (!is_file($filename)) {
                throw new CallbackException("Specified script \"$script\" does not exist in path \"" . getcwd() . "\"");
            }
            $this->script = $filename;
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
        $cmd .= self::makeParamsString(['scriptOptions' => self::encodeParams($options)]);
        pclose(
            popen($cmd . " &", 'w')
        );
    }

    /**
     * Joins two calls: parseCommandLineParameters and decodeParams
     *
     * @param $argv
     * @return array|mixed
     * @throws CallbackException
     */
    public static function getCallOptions($argv)
    {
        $options = Script::parseCommandLineParameters($argv);
        $options = Script::decodeParams($options['scriptOptions']);
        return $options;
    }

    /**
     * Parse parameters from array (usually command line of scripts)
     *
     * @param $argv
     * @return array
     * @throws \zaboy\scheduler\Callback\CallbackException
     */
    public static function parseCommandLineParameters($argv)
    {
        // find first parameter
        while(count($argv)) {
            $token = $argv[0];
            if (substr_compare($token, self::PARAMETERS_PREFIX, 0, strlen(self::PARAMETERS_PREFIX)) == 0) {
                break;
            }
            array_shift($argv);
        }
        // if count of rest elements is not even - error
        if ((count($argv) % 2) != 0) {
            throw new CallbackException("Wrong parameters count in command line");
        }
        // parse options
        $options = [];
        for ($i = 0; $i < count($argv); $i += 2) {
            $key = substr($argv[$i], strlen(self::PARAMETERS_PREFIX));
            $value = $argv[$i + 1];
            $options[$key] = $value;
        }
        return $options;
    }

    /**
     * Serializes and encode by base64 algorithm an array of options
     *
     * @param $options
     * @return string
     */
    public static function encodeParams($options)
    {
        return base64_encode(serialize($options));
    }

    /**
     * Decodes an base64 encoded string and unserializes string to array
     *
     * @param $base64String
     * @return mixed
     */
    public static function decodeParams($base64String)
    {
        return unserialize(base64_decode($base64String));
    }

    /**
     * Join all parameters from $options to string for passing them via command line
     *
     * @param $options
     * @return string
     */
    public static function makeParamsString($options)
    {
        $cmd = '';
        foreach ($options as $key => $value) {
            $cmd .= ' ' . self::PARAMETERS_PREFIX . $key . ' ' . $value;
        }
        return $cmd;
    }
}
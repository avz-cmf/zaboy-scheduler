<?php

namespace zaboy\scheduler\FileSystem;

use zaboy\scheduler\Callback\CallbackException;

class CommandLineWorker
{
    const PARAMETERS_PREFIX = '-';

    /**
     * Joins two calls: parseCommandLineParameters and decodeParams
     *
     * @param $argv
     * @return array|mixed
     * @throws CallbackException
     */
    public function getCallOptions($argv)
    {
        $options = self::parseCommandLineParameters($argv);
        if (!isset($options['scriptOptions'])) {
            return [];
        }
        $options = self::decodeParams($options['scriptOptions']);
        return $options;
    }

    /**
     * Parse parameters from array (usually command line of scripts)
     *
     * @param $argv
     * @return array
     * @throws \zaboy\scheduler\Callback\CallbackException
     */
    public function parseCommandLineParameters($argv)
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
    public function encodeParams($options)
    {
        return base64_encode(serialize($options));
    }

    /**
     * Decodes an base64 encoded string and unserializes string to array
     *
     * @param $base64String
     * @return mixed
     */
    public function decodeParams($base64String)
    {
        return unserialize(base64_decode($base64String));
    }

    /**
     * Join all parameters from $options to string for passing them via command line
     *
     * @param $options
     * @return string
     */
    public function makeParamsString($options)
    {
        $cmd = '';
        foreach ($options as $key => $value) {
            $cmd .= ' ' . self::PARAMETERS_PREFIX . $key . ' ' . $value;
        }
        return $cmd;
    }
}
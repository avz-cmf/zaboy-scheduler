<?php

namespace zaboy\scheduler\Callback\Decorators;

use zaboy\scheduler\Callback\CallbackException;
use zaboy\scheduler\Callback\Script;

class AsyncDecorator extends Script
{
    const SCRIPT_NAME = 'scripts/scriptProxy.php';

    /**
     * @var array - the options from config for passing to the callback
     */
    protected $rpcCallback;


    /**
     * AsyncDecorator constructor.
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

        $cmd = $this->commandPrefix . ' ' . $this->script;
        $cmd .= self::makeParamsString(['scriptOptions' => self::encodeParams($options)]);

        if (substr(php_uname(), 0, 7) == "Windows"){
            pclose(popen($cmd, "r"));
        }
        else {
            exec($cmd . " > /dev/null");
        }
    }
}
<?php

namespace zaboy\scheduler\Callback;

class ScriptProxy extends Script
{
    const SCRIPT_NAME = 'scripts/scriptProxy.php';

    /**
     * @var array - the options from config for passing to the callback
     */
    protected $callbackOptions;

    /**
     * {@inherit}
     *
     * {@inherit}
     */
    public function __construct(array $params = [])
    {
        if (!is_file(self::SCRIPT_NAME)) {
            throw new CallbackException("The handler script \"scriptProxy.php\" does not exist in the folder \"script\"");
        }
        if (!isset($params['rpc_callback'])) {
            throw new CallbackException("The necessary parameter \"rpc_callback\" is expected");
        }
        parent::__construct(array_merge([
            'script_name' => self::SCRIPT_NAME],
            $params
        ));

        $this->callbackOptions = $params;
    }

    /**
     * {@inherit}
     *
     * {@inherit}
     */
    public function call(array $options = [])
    {
        // Merge the options from config with passed options
        $options = array_merge($this->callbackOptions, $options);
        parent::call($options);
    }
}
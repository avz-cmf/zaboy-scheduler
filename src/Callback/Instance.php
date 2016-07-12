<?php

namespace zaboy\scheduler\Callback;

use zaboy\scheduler\Callback\CallbackException;
use zaboy\scheduler\Callback\Interfaces\CallbackInterface;

/**
 * Class Callback\Instance
 *
 * Implements an abstraction of callback - object instance and its method (not static)
 *
 * @see \zaboy\scheduler\Callback\Factory\InstanceAbstractFactory
 * @package zaboy\scheduler\Callback
 */
class Instance implements CallbackInterface
{
    /**
     * Instance whose method will be called
     *
     * @var object
     */
    protected $instance;

    /**
     * Method name
     *
     * @var string
     */
    protected $method;

    /**
     * Instance constructor.
     *
     * @param $instance
     * @param $method
     * @throws \zaboy\scheduler\Callback\CallbackException
     */
    public function __construct($instance, $method)
    {
        // Instance must be an object!!
        $instanceType = gettype($instance);
        if ($instanceType != 'object') {
            throw new CallbackException("The parameter \$instance must be an object. \"{$instanceType}\" given.");
        }
        $this->instance = $instance;
        // Checks if the specified method exists in instance
        if (!method_exists($this->instance, $method)) {
            throw new CallbackException("Specified method \"{$method}\" does not exist in class \""
                . get_class($this->instance) . "\"");
        }
        $this->method = $method;
    }

    /**
     * {@inherit}
     *
     * {@inherit}
     */
    public function call(array $options = [])
    {
        return call_user_func([$this->instance, $this->method], $options);
    }

}
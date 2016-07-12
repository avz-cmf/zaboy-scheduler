<?php

namespace zaboy\scheduler\Callback;

use zaboy\scheduler\Callback\CallbackException;
use zaboy\scheduler\Callback\Interfaces\CallbackInterface;

/**
 * Class Callback\StaticMethod
 *
 * This class implements an abstraction of callback - static method of class
 *
 * @see \zaboy\scheduler\Callback\Factory\StatichMethodAbstractFactory
 * @package zaboy\scheduler\Callback
 */
class StaticMethod implements CallbackInterface
{
    protected $method;

    /**
     * StaticMethod constructor.
     *
     * @param callable $method
     * @throws CallbackException
     */
    public function __construct(callable $method)
    {
        $realMethodName = join('::', (array) $method);
        if (!is_callable($realMethodName) || !strpos($realMethodName, '::')) {
            throw new CallbackException("The specified method \"{$realMethodName}\" is not callable or it's not static");
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
        return call_user_func($this->method, $options);
    }
}
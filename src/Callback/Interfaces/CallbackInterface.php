<?php

namespace zaboy\scheduler\Callback\Interfaces;

/**
 * Interface for various instances of Callback
 *
 * Interface CallbackInterface
 * @package zaboy\scheduler\Callback\Interfaces
 */
interface CallbackInterface
{
    /**
     * Start initialize for object.
     *
     * This method is used in abstract factory of each class during creating the object.
     * @param array $params - associative array where passed data for initialize object and/or those data which won't be changed in future
     * @return void
     */
    public function __construct(array $params = []);

    /**
     * Call the callback.
     *
     * @param array $options - a dynamic data for passing to the callback
     * @return mixed
     */
    public function call(array $options = []);
}
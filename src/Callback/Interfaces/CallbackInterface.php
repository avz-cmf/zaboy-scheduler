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
     * Call the callback.
     *
     * @param array $options - a dynamic data for passing to the callback
     * @return mixed
     */
    public function call(array $options = []);
}
<?php

namespace zaboy\scheduler\Callback\Decorators\Interfaces;

use zaboy\async\Promise\Interfaces\AsyncInterface;
use zaboy\async\Promise\Interfaces\PromiseInterface;

interface AsyncDecoratorInterface extends AsyncInterface
{
    /**
     * {@inherit}
     *
     * @param array $parameter
     * @return PromiseInterface
     */
    public function asyncCall(array $parameter = []);
}
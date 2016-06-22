<?php

namespace zaboy\scheduler\Middleware;

use Zend\Stratigility\MiddlewarePipe;

class Callback extends MiddlewarePipe
{

    /**
     * Callback constructor.
     */
    public function __construct(array $middlewares = [])
    {
        parent::__construct();
        foreach ($middlewares as $middleware) {
            $this->pipe($middleware);
        }
    }
}
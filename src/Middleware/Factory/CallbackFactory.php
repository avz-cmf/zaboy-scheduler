<?php

namespace zaboy\scheduler\Middleware\Factory;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use zaboy\rest\FactoryAbstract;
use zaboy\scheduler\Middleware\Callback;
use Zend\Stratigility\Http\ResponseInterface;
use Zend\Stratigility\Next;

class CallbackFactory extends FactoryAbstract
{
    public function __construct($addMiddlewares = [])
    {
        $this->middlewares = $addMiddlewares;
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $callbackMiddlewareSimple = function (
            ServerRequestInterface $request,
            ResponseInterface $response,
            Next $next = null
        ) {
            $response->withStatus(200);
            $response->getBody()->write("I'm HttpCallback simple response!");
            if ($next) {
                return $next($request, $response);
            }
            return $response;
        };
        $this->middlewares[] = $callbackMiddlewareSimple;
        return new Callback($this->middlewares);
    }

}
<?php

chdir(dirname(__DIR__));
require './vendor/autoload.php';

$container = include './config/container.php';

use Zend\Stratigility\MiddlewarePipe;
use Zend\Diactoros\Server;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Stratigility\Http\ResponseInterface;
use Zend\Stratigility\Next;

$callbackMiddleware = function (
    ServerRequestInterface $request,
    ResponseInterface $response,
    Next $next
) {
    $response->withStatus(200);
    $response->getBody()->write(json_encode("I'm HttpCallback simple response!"));
    $response->withHeader('X-Path', $request->getUri()->getPath());
    if ($next) {
        return $next($request, $response);
    }
    return $response;
};

$app = new MiddlewarePipe();
$app->pipe('/api/callback', $callbackMiddleware);

$server = Server::createServer($app, $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
$server->listen();

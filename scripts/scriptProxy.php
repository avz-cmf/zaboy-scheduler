<?php

$path = getcwd();
if (!is_file($path . '/vendor/autoload.php')) {
    $path = dirname(getcwd());
}
chdir($path);
require $path . '/vendor/autoload.php';

use \zaboy\scheduler\FileSystem\CommandLineWorker;
use \zaboy\scheduler\Callback\CallbackException;
use zaboy\async\Promise\Store;
use zaboy\async\Promise\Client as Promise;

$commandLineWorker = new CommandLineWorker();
$options = $commandLineWorker->getCallOptions($_SERVER['argv']);

if (!isset($options['rpc_callback'])) {
    throw new CallbackException("The necessary parameter \"rpc_callback\" does not exist");
}
$callbackServiceName = $options['rpc_callback'];
unset($options['rpc_callback']);


/** @var Zend\ServiceManager\ServiceManager $container */
$container = include './config/container.php';
/** @var zaboy\scheduler\Callback\CallbackManager $callbackManager */
$callbackManager = $container->get('callback_manager');

/** @var Store $store */
$store = $container->get('Store');
$promise = new Promise($store, $options['promise']);
unset($options['promise']);

try {
    if (is_callable($callbackServiceName)) {
        $result = call_user_func($callbackServiceName, $options);
    } elseif ($callbackManager->has($callbackServiceName)) {
        $result = $callbackManager->{$callbackServiceName}($options);
    } else {
        throw new CallbackException('Specified callback "' . print_r($callbackServiceName) . '" wasn\'t found');
    }
    $promise->resolve($result);
} catch (\Exception $e) {
    $promise->reject($e);
}


<?php

$path = getcwd();
if (!is_file($path . '/vendor/autoload.php')) {
    $path = dirname(getcwd());
}
chdir($path);
require $path . '/vendor/autoload.php';

use \zaboy\scheduler\Callback\ScriptProxy;
use \zaboy\scheduler\Callback\CallbackException;

$options = ScriptProxy::getCallOptions($_SERVER['argv']);
if (!isset($options['rpc_callback'])) {
    throw new CallbackException("The necessary parameter \"rpc_callback\" does not exist");
}
$callbackServiceName = $options['rpc_callback'];
unset($options['rpc_callback']);

/** @var Zend\ServiceManager\ServiceManager $container */
$container = include './config/container.php';
/** @var zaboy\scheduler\Callback\CallbackManager $callbackManager */
$callbackManager = $container->get('callback_manager');

if (!$callbackManager->has($callbackServiceName)) {
    throw new CallbackException("The specified service name for callback \"{$callbackServiceName}\" was not found");
}

/** @var zaboy\scheduler\Callback\Interfaces\CallbackInterface $callback */
$callback = $callbackManager->get($callbackServiceName);
$callback->call($options);
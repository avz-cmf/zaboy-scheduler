<?php

$path = getcwd();
if (!is_file($path . '/vendor/autoload.php')) {
    $path = dirname(getcwd());
}
chdir($path);
require $path . '/vendor/autoload.php';

use \zaboy\scheduler\FileSystem\CommandLineWorker;
use \zaboy\scheduler\Callback\CallbackException;
use zaboy\async\Promise\Adapter\MySqlPromiseAdapter;
use zaboy\async\Promise\PromiseClient;

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

/** @var MySqlPromiseAdapter $mySqlPromiseAdapter */
$mySqlPromiseAdapter = $container->get('MySqlPromiseAdapter');
$options['promise'] = new PromiseClient($mySqlPromiseAdapter, $options['promise']);

$callbackManager->{$callbackServiceName}($options);
<?php

chdir(getcwd());
require './vendor/autoload.php';

use zaboy\scheduler\Callback\Script;
use zaboy\scheduler\DataStore\UTCTime;
use zaboy\rest\DataStore\DataStoreAbstract;

$options = Script::getCallOptions($_SERVER['argv']);
$delay = isset($options['delay']) ?: 2;
sleep($delay);

$container = include './config/container.php';
/** @var DataStoreAbstract $dataStore */
$dataStore = $container->get('pids_datastore');

$itemData = [
    'pid' => posix_getpid(),
    'startedAt' => UTCTime::getUTCTimestamp(),
    'scriptName' => "testWorkerCallback.php",
    'timeout' => 30
];
$dataStore->create($itemData);
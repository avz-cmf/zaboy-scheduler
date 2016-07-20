<?php

chdir(dirname(__DIR__));
require './vendor/autoload.php';

use \zaboy\scheduler\FileSystem\CommandLineWorker;
use \Xiag\Rql\Parser\Node\LimitNode;
use \Xiag\Rql\Parser\Node\SortNode;
use \Xiag\Rql\Parser\Node\SelectNode;

$commandLineWorker = new CommandLineWorker();
$options = $commandLineWorker->getCallOptions($_SERVER['argv']);

/** @var Zend\ServiceManager\ServiceManager $container */
$container = include './config/container.php';
if (!$container->has($serviceName)) {
    throw new Exception("The service \"{$serviceName}\" must be specified in config/datastore");
}
$log = $container->get($serviceName);

// Writes to log
$itemData = array_flip($columns);
array_walk($itemData, function (&$item, $key) use ($options) {
    if (!isset($options[$key])) {
        throw new Exception("Expected necessary parameter \"{$key}\"");
    }
    $item = $options[$key];
});
$log->create($itemData);

// Clears old records in the log
if (isset($options['max_log_rows'])) {
    $maxLogRows = $options['max_log_rows'];
} else {
    $config = $container->get('config')['ticker'][$scriptType]['callbackParams'];
    $maxLogRows = $config['max_log_rows'];
}

$query = new \Xiag\Rql\Parser\Query();
$query->setSelect(new SelectNode(['id']));
$query->setLimit(new LimitNode($maxLogRows, $maxLogRows));
$query->setSort(new SortNode(['id' => '-1']));

$rowsForDeleting = $log->query($query);
foreach ($rowsForDeleting as $row) {
    $log->delete($row['id']);
}
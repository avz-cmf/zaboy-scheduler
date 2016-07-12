<?php

namespace zaboy\test\scheduler\Examples\Callback;

use Interop\Container\ContainerInterface;
use zaboy\scheduler\DataStore\UTCTime;

class SimpleClass
{
    /** @var \Interop\Container\ContainerInterface $container */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    public function methodWhichLogsOneRow(array $options = [])
    {
        $log = $this->container->get('tick_log_datastore');

        $itemData = [
            'tick_id' => UTCTime::getUTCTimestamp(),
            'step' => preg_replace("/\s+/", " ", var_export($options, 1)),
        ];
        $log->create($itemData);
    }

    public static function staticMethodWhichLogsOneRow(array $options = [])
    {
        $container = include './config/container.php';
        $log = $container->get('tick_log_datastore');

        $itemData = [
            'tick_id' => UTCTime::getUTCTimestamp(),
            'step' => preg_replace("/\s+/", " ", var_export($options, 1)),
        ];
        $log->create($itemData);
    }
}
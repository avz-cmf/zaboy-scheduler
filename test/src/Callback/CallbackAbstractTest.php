<?php

namespace zaboy\test\Callback;

abstract class CallbackAbstractTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Zend\ServiceManager\ServiceManager $container */
    protected $container;

    /** @var  \zaboy\rest\DataStore\DataStoreAbstract */
    protected $log;

    /** @var \zaboy\scheduler\Callback\Interfaces\CallbackInterface $callback */
    protected $callback;

    protected function setUp()
    {
        $this->container = include './config/container.php';
        $this->log = $this->container->get('tick_log_datastore');
        $this->initCallback();
    }

    protected function tearDown()
    {
        $this->log->deleteAll();
    }

    abstract protected function initCallback();

    abstract public function test_call();
}
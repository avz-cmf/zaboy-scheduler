<?php

namespace zaboy\test\scheduler\Callback\Decorators;

use Xiag\Rql\Parser\Query;

class DecoratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Zend\ServiceManager\ServiceManager $container */
    protected $container;

    /** @var  \zaboy\rest\DataStore\DataStoreAbstract $log */
    protected $log;

    /** @var \zaboy\scheduler\Callback\Decorators\ScriptDecorator $decorator */
    protected $decorator;

    protected function setUp()
    {
        $this->container = include './config/container.php';
        $this->log = $this->container->get('tick_log_datastore');
        $this->log->deleteAll();
    }

    protected function tearDown()
    {
        $this->log->deleteAll();
    }


    public function test_callScript()
    {
        $this->decorator = $this->container->get('test_async_decorator_with_script_callback');
        $promise = $this->decorator->asyncCall([
            'tick_id' => time(),
            'step' => 1
        ]);
        $this->assertEquals(
            $promise->getState(), $promise::PENDING
        );
        sleep(2);
        $this->assertEquals(
            $promise->getState(), $promise::FULFILLED
        );
        $this->assertEquals(1, $this->log->count());
    }

    public function test_callInstance()
    {
        $this->decorator = $this->container->get('test_async_decorator_with_instance_callback');
        $promise = $this->decorator->asyncCall([
            'tick_id' => time(),
            'step' => 1
        ]);
        $this->assertEquals(
            $promise->getState(), $promise::PENDING
        );
        sleep(2);
        $this->assertEquals(
            $promise->getState(), $promise::FULFILLED
        );
        $this->assertEquals(1, $this->log->count());
    }

    public function test_callStaticMethod()
    {
        $this->decorator = $this->container->get('test_async_decorator_with_staticmethod_callback');
        $promise = $this->decorator->asyncCall([
            'tick_id' => time(),
            'step' => 1
        ]);
        $this->assertEquals(
            $promise->getState(), $promise::PENDING
        );
        sleep(2);
        $this->assertEquals(
            $promise->getState(), $promise::FULFILLED
        );
        $this->assertEquals(1, $this->log->count());
    }
}
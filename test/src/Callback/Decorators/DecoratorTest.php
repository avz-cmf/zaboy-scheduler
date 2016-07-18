<?php

namespace zaboy\test\scheduler\Callback\Decorators;

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
        $this->decorator->call([
            'tick_id' => time(),
            'step' => 1
        ]);

        $this->assertEquals(1, $this->log->count());
    }

    public function test_callInstance()
    {
        $this->decorator = $this->container->get('test_async_decorator_with_instance_callback');
        $this->decorator->call([
            'tick_id' => time(),
            'step' => 1
        ]);

        $this->assertEquals(1, $this->log->count());
    }

    public function test_callStaticMethod()
    {
        $this->decorator = $this->container->get('test_staticmethod_callback_via_decorator');
        $this->decorator->call([
            'tick_id' => time(),
            'step' => 1
        ]);

        $this->assertEquals(1, $this->log->count());
    }
}
<?php

namespace zaboy\test\scheduler\Callback\Decorators;

use Xiag\Rql\Parser\Query;
use \zaboy\scheduler\Callback\Decorators\ScriptDecorator;

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
        $this->decorator = $this->container->get('script_decorator');
    }

    protected function tearDown()
    {
        $this->log->deleteAll();
    }


    public function test_callScript()
    {
        $promise = $this->decorator->asyncCall([
            'rpc_callback' => 'script_tick_callback',
            'tick_id' => time(),
            'step' => 1,
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
        $promise = $this->decorator->asyncCall([
            'rpc_callback' => 'test_instance_callback_via_decorator',
            [
                'tick_id' => time(),
                'step' => 1,
            ]
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
        $promise = $this->decorator->asyncCall([
            'rpc_callback' => 'test_staticmethod_callback_via_decorator',
            'tick_id' => time(),
            'step' => 1,
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


    public function test_callCallable()
    {
        $promise = $this->decorator->asyncCall([
            'rpc_callback' => ['\zaboy\test\scheduler\Examples\Callback\SimpleClass', 'staticMethodWhichLogsOneRow'],
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


    public function test_callWrongCallable()
    {
        $promise = $this->decorator->asyncCall([
            'rpc_callback' => 'some_any_no',
            'tick_id' => time(),
            'step' => 1
        ]);
        $this->assertEquals(
            $promise->getState(), $promise::PENDING
        );
        sleep(2);
        $this->assertEquals(
            $promise->getState(), $promise::REJECTED
        );
        $result = $promise->wait(false);
        $this->assertInstanceOf(
            'zaboy\async\Promise\Exception\RejectedException', $result
        );
    }
}
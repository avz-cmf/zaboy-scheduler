<?php

namespace zaboy\test\scheduler\Broker;

use zaboy\scheduler\Callback\Decorators\ScriptDecorator;

class ScriptBrokerTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Zend\ServiceManager\ServiceManager $container */
    protected $container;

    /** @var  \zaboy\rest\DataStore\DataStoreAbstract $pidsDataStore */
    protected $pidsDataStore;

    protected function setUp()
    {
        $this->container = include './config/container.php';
        $this->pidsDataStore = $this->container->get('pids_datastore');
    }

    /**
     * TODO:
     * - запустить несколько скриптов; из них
     *      - "подвисший" - sleep(60);
     *      - с нормальным завершением;
     *      - с нормальным завершением, но с генерацией нотисов в процессе работы
     *      - с exception'ом;
     *      - с критической ошибкой - trigger_error
     *      - с синтаксической ошибкой
     * - подождать 30 секунд;
     * - проверить состояние скриптов, проверить ответы.
     */



    public function test_startProcesses()
    {
        /** @var ScriptDecorator $decorator */
        $decorator = $this->container->get('test_scriptBroker_decorator_critical_error');
        $decorator->call();

        $decorator = $this->container->get('test_scriptBroker_decorator_exception');
        $decorator->call();

        $decorator = $this->container->get('test_scriptBroker_decorator_long_work');
        $decorator->call();

        $decorator = $this->container->get('test_scriptBroker_decorator_normal');
        $decorator->call();

        $decorator = $this->container->get('test_scriptBroker_decorator_normal_with_warning');
        $decorator->call();

        $decorator = $this->container->get('test_scriptBroker_decorator_syntax_error');
        $decorator->call();

        $this->assertEquals(
            6, $this->pidsDataStore->count()
        );
    }

    public function test_checkProcess()
    {
        // Let finish to processes
        sleep(2);
        /** @var \zaboy\scheduler\Broker\ScriptBroker $broker */
        $broker = $this->container->get('script_broker');
        $broker->checkProcess();
        $this->assertEquals(
            1, $this->pidsDataStore->count()
        );

        sleep (30);
        $broker->checkProcess();
        $this->assertEquals(
            0, $this->pidsDataStore->count()
        );
    }
}
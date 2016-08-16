<?php

namespace zaboy\test\scheduler\Broker;

use zaboy\scheduler\Callback\Decorators\ScriptDecorator;

class ScriptBrokerTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Zend\ServiceManager\ServiceManager $container */
    protected $container;

    /** @var  \zaboy\rest\DataStore\DataStoreAbstract $pidsDataStore */
    protected $pidsDataStore;

    /** @var \zaboy\scheduler\Callback\Decorators\ScriptDecorator $decorator */
    protected $decorator;

    protected function setUp()
    {
        $this->container = include './config/container.php';
        $this->pidsDataStore = $this->container->get('pids_datastore');
        $this->decorator = $this->container->get('script_decorator');
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
        $this->decorator->asyncCall([
            'rpc_callback' => 'test_scriptBroker_script_critical_error',
        ]);

        $this->decorator->asyncCall([
            'rpc_callback' => 'test_scriptBroker_script_exception',
        ]);

        $this->decorator->asyncCall([
            'rpc_callback' => 'test_scriptBroker_script_long_work',
        ]);

        $this->decorator->asyncCall([
            'rpc_callback' => 'test_scriptBroker_script_normal',
        ]);

        $this->decorator->asyncCall([
            'rpc_callback' => 'test_scriptBroker_script_normal_with_warning',
        ]);

        $this->decorator->asyncCall([
            'rpc_callback' => 'test_scriptBroker_script_syntax_error',
        ]);

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
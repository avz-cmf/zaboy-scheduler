<?php

namespace zaboy\test\Callback;

class ScriptProxyCallbackTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Zend\ServiceManager\ServiceManager $container */
    protected $container;

    /** @var  \zaboy\rest\DataStore\DataStoreAbstract */
    protected $log;

    protected function setUp()
    {
        $this->container = include './config/container.php';
        $this->log = $this->container->get('tick_log_datastore');
    }

    public function test_scriptProxy()
    {
        // Clear log before testing
        $this->log->deleteAll();

        $callbackManager = $this->container->get('callback_manager');
        /** @var \zaboy\scheduler\Callback\Script $scriptCallback */
        $scriptProxyCallback = $callbackManager->get('test_scriptproxy_callback');
        $options = [
            'param1' => 'value1',
            'param2' => ['value21', 'value22'],
        ];
        $scriptProxyCallback->call($options);

        // Expected that in the log will be one entry
        $item = $this->log->read(1);
        $this->assertEquals(
            print_r($options, 1), $item['step']
        );

        // Clear log again
        $this->log->deleteAll();
    }
}
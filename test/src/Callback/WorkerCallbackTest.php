<?php

namespace zaboy\test\scheduler\Callback;

use zaboy\scheduler\Callback\CallbackException;
use zaboy\scheduler\Callback\Worker;
use zaboy\scheduler\DataStore\UTCTime;

class WorkerCallbackTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Zend\ServiceManager\ServiceManager $container */
    protected $container;

    /** @var  \zaboy\rest\DataStore\DataStoreAbstract */
    protected $dataStore;

    protected $workerServiceName = 'worker_example_callback';

    protected function setUp()
    {
        $this->container = include './config/container.php';
        $this->dataStore = $this->container->get('pids_datastore');
    }

    public function test_runScriptWithDelayLessThanHere()
    {
        // Clear log before testing
        $this->dataStore->deleteAll();

        if ('Windows' == substr(php_uname(), 0, 7)) {
            $this->setExpectedExceptionRegExp('Zend\ServiceManager\Exception\ServiceNotCreatedException');
        }

        /** @var Worker $worker */
        try {
            $worker = $this->container->get($this->workerServiceName);
            $pId = $worker->call([
                'delay' => 3
            ]);
        } catch (CallbackException $e) {
            var_dump($e);
        }
        $this->assertTrue(
            $worker->isProcessWorking($pId)
        );
        sleep(5);

        $itemData = [
            'pid' => $pId,
            'startedAt' => UTCTime::getUTCTimestamp(),
            'scriptName' => 'Worker: runScriptWithDelayLessThanHere',
            'timeout' => 30
        ];
        $this->dataStore->create($itemData);

        $this->assertFalse(
            $worker->isProcessWorking($pId)
        );

        // Two records in the log
        $this->assertEquals(
            2, $this->dataStore->count()
        );
    }

    public function test_runScriptWithDelayMoreThanHere()
    {
        if ('Windows' == substr(php_uname(), 0, 7)) {
            $this->setExpectedExceptionRegExp('Zend\ServiceManager\Exception\ServiceNotCreatedException');
        }

        $worker = $this->container->get($this->workerServiceName);
        $pId = $worker->call([
            'delay' => 10
        ]);
        $this->assertTrue(
            $worker->isProcessWorking($pId)
        );
//        sleep(5);

        $itemData = [
            'pid' => $pId,
            'startedAt' => UTCTime::getUTCTimestamp(),
            'scriptName' => 'Worker: runScriptWithDelayMoreThanHere',
            'timeout' => 30
        ];
        $this->dataStore->create($itemData);

        $this->assertTrue(
            $worker->isProcessWorking($pId)
        );
        // Must be three records in the log: scrpit's still working
        $this->assertEquals(
            3, $this->dataStore->count()
        );
    }

    public function test_tryingToKillScript()
    {
        if ('Windows' == substr(php_uname(), 0, 7)) {
            $this->setExpectedExceptionRegExp('Zend\ServiceManager\Exception\ServiceNotCreatedException');
        }

        $worker = $this->container->get($this->workerServiceName);
        $pId = $worker->call([
            'delay' => 10
        ]);
        $this->assertTrue(
            $worker->isProcessWorking($pId)
        );
        $itemData = [
            'pid' => $pId,
            'startedAt' => UTCTime::getUTCTimestamp(),
            'scriptName' => 'Worker: tryingToKillScript',
            'timeout' => 30
        ];
        $this->dataStore->create($itemData);

        // Trying to kill script
        posix_kill($pId, 9);
        // Give script the time for dying
        sleep(1);

        // Checks it
        $this->assertFalse(
            $worker->isProcessWorking($pId)
        );

        sleep(6);

        // Five records in the log: plus records after previous test
        // and in this test script won't write the entry: it was killed
        $this->assertEquals(
            5, $this->dataStore->count()
        );
    }
}
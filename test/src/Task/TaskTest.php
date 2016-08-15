<?php

namespace zaboy\test\scheduler\Task;

use Xiag\Rql\Parser\Node\Query\LogicOperator\OrNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\EqNode;
use Xiag\Rql\Parser\Query;
use zaboy\scheduler\DataStore\UTCTime;
use zaboy\scheduler\Task\Broker;
use zaboy\scheduler\Task\Client;

class TaskTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Zend\ServiceManager\ServiceManager $container */
    protected $container;

    /** @var Broker $broker */
    protected $broker;

    /** @var Client $task */
    protected $task;

    protected function setUp()
    {
        $this->container = include './config/container.php';
        $this->broker = $this->container->get('task_broker');
    }


    protected function _initTask()
    {
        $seconds = [];
        $currentTime = date('s');
        for ($i = 0; $i < 2; $i++) {
            $seconds[] = new EqNode('seconds', $currentTime + $i);
        }
        $query = new Query();
        $query->setQuery(new OrNode($seconds));
        $this->task = $this->broker->makeTask([
            'schedule' => $query,
            'callback' => function() {
                return 'ky';
            }
        ]);
    }


    public function test_createTask()
    {
        $this->_initTask();

        $time = date('s');
        $this->assertEquals(
            'ky', $this->task->activate(UTCTime::getUTCTimestamp(0), 1)
        );
    }


    public function test_updateTaskSchedule()
    {
        $this->_initTask();

        $seconds = [];
        $currentTime = date('i');
        for ($i = 0; $i < 2; $i++) {
            $seconds[] = new EqNode('minutes', $currentTime + $i);
        }

        $query = new Query();
        $query->setQuery(new OrNode($seconds));
        $this->task->setSchedule($query);

        $this->assertEquals(
            'ky', $this->task->activate(UTCTime::getUTCTimestamp(0), 1)
        );
    }


    public function test_updateTaskCallback()
    {
        $this->_initTask();

        $this->task->setCallback(function() {
            return 'ky-ky';
        });
        $this->assertEquals(
            'ky-ky', $this->task->activate(UTCTime::getUTCTimestamp(0), 1)
        );
    }


    public function test_setTaskActive()
    {
        $this->_initTask();

        $this->assertEquals(
            'ky', $this->task->activate(UTCTime::getUTCTimestamp(0), 1)
        );

        $this->task->setActive(false);

        $this->assertNull(
            $this->task->activate(UTCTime::getUTCTimestamp(0), 1)
        );
    }


    public function test_deleteTask()
    {
        $this->_initTask();

        $this->assertEquals(
            'ky', $this->task->activate(UTCTime::getUTCTimestamp(0), 1)
        );

        $this->broker->deleteTask($this->task->getId());

        $this->assertNull(
            $this->task->activate(UTCTime::getUTCTimestamp(0), 1)
        );
    }
}
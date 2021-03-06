<?php

namespace zaboy\test\scheduler\Scheduler;

use Xiag\Rql\Parser\Node\Query\ScalarOperator\EqNode;
use Xiag\Rql\Parser\Query;
use zaboy\rest\DataStore\DbTable;
use zaboy\rest\DataStore\Memory;
use zaboy\scheduler\Ticker\Ticker;

class SchedulerTest extends \PHPUnit_Framework_TestCase
{
    protected $callbackServiceName = 'staticmethod_tick_callback';

    /** @var  \Zend\ServiceManager\ServiceManager $container */
    protected $container;

    /** @var  \zaboy\rest\DataStore\DataStoreAbstract $filterDs */
    protected $filterDs;

    /** @var  \zaboy\rest\DataStore\DataStoreAbstract $log */
    protected $log;

    public function setUp()
    {
        $this->container = include './config/container.php';
//        $this->filterDs = $this->container->get('test_scheduler_filters_datastore');
        $this->filterDs = $this->container->get('filters_datastore');

        $this->log = $this->container->get('tick_log_datastore');
        $this->log->deleteAll();

//        $this->setFilters();
    }

//    protected function setFilters()
//    {
//        $this->filterDs->deleteAll();
//        $filterData = [
//            'rql' => 'in(seconds,(3,8,10,15,20,23,33,41,55,59))',
//            'callback' => 'tick_callback',
//            'active' => 1
//        ];
//        $this->filterDs->create($filterData);
//
//        $filterData = [
//            'rql' => 'in(seconds,(4,9,11,16,21,24,34,42,56))',
//            'callback' => 'tick_callback',
//            'active' => 1
//        ];
//        $this->filterDs->create($filterData);
//    }
//
//    /**
//     * @param array $options
//     * @return Ticker
//     */
//    protected function setTicker($options = [])
//    {
//        $config = $this->container->get('config')['test_schedule_callback'];
//        $hopCallback = $this->container->get($config['hop']['callback']);
//        $tickCallback = $this->container->get($config['tick']['callback']);
//
//        // Command line options have higher priority
//        $options = array_merge($config, $options);
//        $ticker = new Ticker($tickCallback, $hopCallback, $options);
//        return $ticker;
//    }
//
//    public function test_countCallingCallback()
//    {
//        $this->setTicker()
//            ->start();
//        $this->assertEquals(19, $this->log->count());
//    }
//
//    public function test_withStepInQueryLessThanStepFromTicker()
//    {
//        $this->setExpectedExceptionRegExp('zaboy\scheduler\Scheduler\SchedulerException');
//        $filterData = [
//            'id' => 3,
//            'rql' => 'and(in(seconds,(4,9,11,16,21,24,34,42,56)),eq(tp_seconds,0))',
//            'callback' => 'tick_callback',
//            'active' => 1
//        ];
//        $item = $this->filterDs->create($filterData, true);
//        try {
//            $this->setTicker()
//                ->start();
//        } catch (\Exception $e) {
//            $exceptionClass = get_class($e);
//            throw new $exceptionClass($e->getMessage(), $e->getCode());
//        } finally {
//            $this->filterDs->delete($item['id']);
//        }
//    }

    public function test_createFilterTask()
    {
        /** @var \zaboy\scheduler\Scheduler\Scheduler $scheduler */
        $scheduler = $this->container->get('scheduler');

        $filterData = $scheduler->create([
            'rql' => 'eq(tp_seconds,0)',
            'callback' => function() {
                return 'ky';
            },
            'active' => 1
        ]);
        $callbackServiceNameFromDataStore = $this->filterDs->read($filterData['id'])['callback'];
        $this->assertEquals($filterData['callback'], $callbackServiceNameFromDataStore);

        $filterData = $scheduler->read($filterData['id']);
        $callback = $filterData['callback'];
        $this->assertEquals(
            'ky', $callback()
        );
    }


    public function test_updateFilterTask()
    {
        /** @var \zaboy\scheduler\Scheduler\Scheduler $scheduler */
        $scheduler = $this->container->get('scheduler');
        $filterData = $scheduler->create([
            'rql' => 'eq(tp_seconds,0)',
            'callback' => function() {
                return 'ky';
            },
            'active' => 1
        ]);

        $id = $filterData['id'];

        $filterDataAfterUpdate = $scheduler->update([
            'id' => $id,
            'rql' => 'eq(seconds,0)'
        ]);

        $this->assertEquals(
            $filterData['callback'], $filterDataAfterUpdate['callback']
        );
        $this->assertEquals(
            'eq(seconds,0)', $filterDataAfterUpdate['rql']
        );

        $scheduler->update([
            'id' => $id,
            'callback' => function() {
                return 'ky-ky';
            },
        ]);
        $filterData = $scheduler->read($filterData['id']);
        $callback = $filterData['callback'];
        $this->assertEquals(
            'ky-ky', $callback()
        );
    }


    public function test_queryFilterTask()
    {
        /** @var \zaboy\scheduler\Scheduler\Scheduler $scheduler */
        $scheduler = $this->container->get('scheduler');
        $filterData = $scheduler->create([
            'rql' => 'eq(tp_seconds,0)',
            'callback' => function() {
                return 'ky';
            },
            'active' => 1
        ]);
        $id = $filterData['id'];

        $query = new Query();
        $query->setQuery(new EqNode(
            'id', $id
        ));
        $items = $scheduler->query($query);
        $item = array_shift($items);
        $callback = $item['callback'];
        $this->assertEquals(
            'ky', $callback()
        );
    }
}
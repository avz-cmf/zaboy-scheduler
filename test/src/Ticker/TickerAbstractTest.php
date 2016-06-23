<?php

namespace zaboy\test\sheduler\Ticker;

use zaboy\scheduler\Callback\StaticMethod;
use zaboy\scheduler\Ticker\Ticker;

abstract class TickerAbstractTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \zaboy\rest\DataStore\DataStoreAbstract */
    protected $tickLog;

    /** @var  \zaboy\rest\DataStore\DataStoreAbstract */
    protected $hopLog;

    /** @var  \Zend\ServiceManager\ServiceManager $container */
    protected $container;

    protected $tickerServiceName;

    /** @var  \zaboy\scheduler\Ticker\Ticker */
    protected $ticker;

    /** @var  \zaboy\scheduler\FileSystem\FileManagerCsv $fileManager */
    protected $fileManager;

    protected function setTicker($options = [])
    {
        $config = $this->container->get('config')[$this->tickerServiceName];
        $hopCallback = $this->container->get($config['hop']['callback']);
        $tickCallback = $this->container->get($config['tick']['callback']);

        // Command line options have higher priority
        $options = array_merge($config, $options);
        $this->ticker = new Ticker($tickCallback, $hopCallback, $options);
    }

    protected function setUp()
    {
        $this->container = include './config/container.php';
        $this->fileManager = $this->container->get('file_manager_csv');
        $this->setLogFiles();
        $_SERVER['argv'] = (array) array_shift($_SERVER['argv']);

        $this->tickLog = $this->container->get('tick_log_datastore');
        $this->hopLog = $this->container->get('hop_log_datastore');
    }

    protected function setLogFiles()
    {
        $dataStoreConfig = $this->container->get('config')['dataStore'];

        if (!$this->fileManager->has($dataStoreConfig['hop_log_datastore']['filename'])) {
            $this->fileManager->create(
                $dataStoreConfig['hop_log_datastore']['filename'],
                ['id', 'hop_start', 'ttl']
            );
        }
        if (!$this->fileManager->has($dataStoreConfig['tick_log_datastore']['filename'])) {
            $this->fileManager->create(
                $dataStoreConfig['tick_log_datastore']['filename'],
                ['id', 'tick_id', 'step']
            );
        }
        /*// Create log files if they do not exist
        if (!is_file($dataStoreConfig['hop_log_datastore']['filename'])) {
            copy(
                $dataStoreConfig['hop_log_datastore']['filename'] . '.dist',
                $dataStoreConfig['hop_log_datastore']['filename']
            );
        }
        if (!is_file($dataStoreConfig['tick_log_datastore']['filename'])) {
            copy(
                $dataStoreConfig['tick_log_datastore']['filename'] . '.dist',
                $dataStoreConfig['tick_log_datastore']['filename']
            );
        }*/
    }

    /**
     * This test may not work in virtual environment like windows web-server (denwer, openserver etc) or vbox virtual machines
     */
    public function test_tickerInTenthsPartOfSeconds()
    {
        $this->tickLog->deleteAll();
        $this->hopLog->deleteAll();

        $this->setTicker([
            'total_time' => 3,
            'step' => 0.1,
            'critical_overtime' => 100,
        ]);
        $this->ticker->start();

        $this->assertEquals(30, $this->tickLog->count());
        $this->assertEquals(1, $this->hopLog->count());
    }

    public function test_tickerWithBigStep()
    {
        $this->tickLog->deleteAll();
        $this->hopLog->deleteAll();

        $this->setTicker([
            'total_time' => 10,
            'step' => 5,
        ]);
       $this->ticker->start();

        $this->assertEquals(2, $this->tickLog->count());
        $this->assertEquals(1, $this->hopLog->count());
    }

    /**
     * This test may not work in virtual environment like windows web-server (denwer, openserver etc) or vbox virtual machines
     */
    public function test_clearLog()
    {
        // Add limits for log files
        $this->setTicker([
            'total_time' => 3,
            'step' => 0.1,
            'tick__max_log_rows' => 30,
            'hop__max_log_rows' => 1,
            'critical_overtime' => 100,
        ]);
        $this->ticker->start();

        $this->assertEquals(
            30, $this->tickLog->count()
        );
        $this->assertEquals(
            1, $this->hopLog->count()
        );
    }


    public static function longCallback()
    {
        usleep(200000);
    }

    public function test_checkOverheadTime()
    {
        $hopCallback = new StaticMethod(['method' => __CLASS__ . '::longCallback']);
        $tickCallback = new StaticMethod(['method' => __CLASS__ . '::longCallback']);
        $options = [
            'total_time' => 3,
            'step' => 0.1,
        ];
        $ticker = new Ticker($tickCallback, $hopCallback, $options);
        $this->setExpectedExceptionRegExp('\Exception', "/Current hop already works greater than allowed overhead time/");
        $ticker->start();
    }
}

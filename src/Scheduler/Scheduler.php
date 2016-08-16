<?php

namespace zaboy\scheduler\Scheduler;

use Opis\Closure\SerializableClosure;
use zaboy\rest\DataStore\Aspect\AspectAbstract;
use zaboy\rest\RqlParser\RqlParser;
use zaboy\scheduler\Callback\CallbackException;
use zaboy\scheduler\Callback\CallbackManager;
use zaboy\scheduler\Callback\Interfaces\CallbackInterface;
use zaboy\scheduler\DataStore\Timeline;
use Xiag\Rql\Parser\Query;
use zaboy\rest\DataStore\DataStoreAbstract;
use Xiag\Rql\Parser\Node\Query\ScalarOperator;
use Xiag\Rql\Parser\Node\Query\LogicOperator;

class Scheduler extends AspectAbstract
{
    const DEFAULT_FILTERS_DATASTORE_SERVICE_NAME = 'filters_datastore';

    const DEFAULT_TIMELINE_DATASTORE_SERVICE_NAME = 'timeline_datastore';

    /** @var  \zaboy\scheduler\DataStore\Timeline $timelineDs */
    protected $timelineDs;

    /** @var  array $filters */
    protected $filters;

    /** @var \zaboy\scheduler\Callback\CallbackManager $callbackManager */
    protected $callbackManager;

    /**
     * Scheduler constructor.
     *
     * @param DataStoreAbstract $filterDs
     * @param Timeline $timelineDs
     * @param CallbackManager $callbackManager
     */
    public function __construct(DataStoreAbstract $filterDs, Timeline $timelineDs, CallbackManager $callbackManager)
    {
        parent::__construct($filterDs);
        $this->timelineDs = $timelineDs;
        $this->callbackManager = $callbackManager;
    }

    /**
     * Adds limits to query for limiting time range
     *
     * @param Query $query
     * @param $tickId
     * @param $step
     * @return Query
     */
    protected function addTickLimit(Query $query, $tickId, $step)
    {
        $field = ($step < 1) ? 'id' : 'timestamp';
        $andNode = new LogicOperator\AndNode([
            $query->getQuery(),
            new ScalarOperator\GeNode($field, $tickId),
            new ScalarOperator\LtNode($field, $tickId + $step),
        ]);
        $query->setQuery($andNode);
        return $query;
    }

    /**
     * Hop callback which called from Ticker
     *
     * @param $hopId
     * @param $ttl
     */
    public function processHop($hopId, $ttl)
    {
        /**
         * One more reads active filters from DataStore and further use it ever tick
         */
        $query = new Query();
        $query->setQuery(
            new ScalarOperator\EqNode('active', 1)
        );
        $this->filters = $this->dataStore->query($query);
    }

    /**
     * Tick callback which called from Ticker
     *
     * @param $tickId
     * @param $step
     * @throws SchedulerException
     * @throws \zaboy\scheduler\Callback\CallbackException
     */
    public function processTick($tickId, $step)
    {
        $rqlParser = new RqlParser();
        foreach ($this->filters as $filter) {
            // Parses rql-query expression
            $rqlQueryObject = $rqlParser->decode($filter['rql']);
            // Step value determined in Timeline DataStore
            if ($this->timelineDs->determineStep($rqlQueryObject) < $step) {
                throw new SchedulerException("The step determined from query to timeline DataStore is less than step given from Ticker");
            }
            // Adds time limits and executes query to timeline
            $rqlQueryObject = $this->addTickLimit($rqlQueryObject, $tickId, $step);
            $matches = $this->timelineDs->query($rqlQueryObject);
            // If mathces were found runs their callbacks
            if (count($matches)) {
                /** @var CallbackInterface $instance */
                $instance = $this->callbackManager->get($filter['callback']);
                $instance->call(['tick_id' => $tickId, 'step' => $step]);
            }
        }
    }

    protected function preCreate(&$itemData, &$rewriteIfExist = false)
    {
        $itemData['callback'] = $this->prepareCallbackServiceName($itemData['callback']);
        parent::preCreate($itemData, $rewriteIfExist);
    }

    protected function preUpdate(&$itemData, &$createIfAbsent = false)
    {
        if (isset($itemData['callback'])) {
            $itemData['callback'] = $this->prepareCallbackServiceName($itemData['callback']);
        }
        parent::preUpdate($itemData, $createIfAbsent);
    }

    protected function postRead(&$result, $id)
    {
        $result['callback'] = unserialize($result['callback']);
        parent::postRead($result, $id);
    }

    protected function postQuery(&$result, Query $query)
    {
        array_walk($result, function(&$item, $key) {
            $item['callback'] = unserialize($item['callback']);
        });
        parent::postQuery($result, $query);
    }


    protected function prepareCallbackServiceName($callbackServiceName)
    {
        if (is_callable($callbackServiceName)) {
            if (is_string($callbackServiceName) && $this->callbackManager->has($callbackServiceName)) {
                throw new CallbackException('Specified service name is ambiguous:
                    both service name and callable are exist');
            }
            $callbackServiceName = $this->serializeCallback($callbackServiceName);
        } elseif (is_string($callbackServiceName) && !$this->callbackManager->has($callbackServiceName)) {
            throw new CallbackException('Specified callback "' . $callbackServiceName . '" doesn\'t exist');
        }
        return $callbackServiceName;
    }


    /**
     * TODO: дублирование кода
     *
     * @param $callable
     * @return null|string
     */
    protected function serializeCallback($callable)
    {
        if (is_null($callable)) {
            return null;
        }
        if ($callable instanceof \Closure) {
            $callable = new SerializableClosure($callable);
        }
        return serialize($callable);
    }
}
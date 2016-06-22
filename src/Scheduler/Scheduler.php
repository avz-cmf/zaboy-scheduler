<?php

namespace zaboy\scheduler\Scheduler;

use zaboy\rest\RqlParser\RqlParser;
use zaboy\scheduler\Callback\CallbackManager;
use zaboy\scheduler\Callback\Interfaces\CallbackInterface;
use zaboy\scheduler\DataStore\Timeline;
use Xiag\Rql\Parser\Query;
use zaboy\rest\DataStore\DataStoreAbstract;
use Xiag\Rql\Parser\Node\Query\ScalarOperator;
use Xiag\Rql\Parser\Node\Query\LogicOperator;

class Scheduler
{
    const DEFAULT_FILTERS_DATASTORE_SERVICE_NAME = 'filters_datastore';

    const DEFAULT_TIMELINE_DATASTORE_SERVICE_NAME = 'timeline_datastore';

    /** @var \zaboy\rest\DataStore\DataStoreAbstract $filterDs */
    protected $filterDs;

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
        $this->filterDs = $filterDs;
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
        $this->filters = $this->filterDs->query($query);
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
            $rqlQueryObject = $rqlParser->rqlDecoder($filter['rql']);
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
}
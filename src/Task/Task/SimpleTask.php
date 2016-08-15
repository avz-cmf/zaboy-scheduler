<?php

namespace zaboy\scheduler\Task\Task;

use Opis\Closure\SerializableClosure;
use Xiag\Rql\Parser\Query;
use zaboy\async\EntityAbstract;
use zaboy\rest\RqlParser\RqlParser;
use zaboy\scheduler\DataStore\Timeline;
use Xiag\Rql\Parser\Node\Query\LogicOperator;
use Xiag\Rql\Parser\Node\Query\ScalarOperator;
use zaboy\scheduler\Task\Exception\TaskException;

class SimpleTask extends EntityAbstract
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        if ($data['schedule'] instanceof Query) {
            $this->setSchedule($data['schedule']);
        }
        if (is_callable($data['callback'])) {
            $this->setCallback($data['callback']);
        }
    }

    public function getSchedule()
    {
        if (is_string($this->data['schedule'])) {
            $rqlParser = new RqlParser();
            return $rqlParser->decode($this->data['schedule']);
        }
        return $this->data['schedule'];
    }

    public function setSchedule(Query $query)
    {
        $rqlParser = new RqlParser();
        $this->data['schedule'] = $rqlParser->encode($query);
        return $this->data;
    }

    public function getCallback()
    {
        if (is_callable($this->data['callback'])) {
            return $this->data['callback'];
        }
        return unserialize($this->data['callback']);
    }

    public function setCallback(callable $callback)
    {
        if ($callback instanceof \Closure) {
            $callback = new SerializableClosure($callback);
        }
        $this->data['callback'] = serialize($callback);
        return $this->data;
    }

    public function setActive($active)
    {
        $this->data['active'] = $active;
        return $this->data;
    }

    /**
     * @param int|float $timeStart
     * @param int|float $step
     * @param mixed|null $options
     * @return mixed|null
     * @throws TaskException
     */
    public function activate($timeStart, $step, $options = [])
    {
        if (!$this->data['active']) {
            return null;
        }
        $schedule = $this->getSchedule();
        $timeline = new Timeline();

        $maxStep = $timeline->determineStep($schedule);
        if ($maxStep < $step) {
            throw new TaskException("The step determined from query to Timeline DataStore is less
                than step given from parameters: {$maxStep} < {$step}");
        }

        $schedule = $this->addTickLimit($schedule, $timeStart, $step);

        $matches = $timeline->query($schedule);
        // If match was found runs his callback
        $result = null;
        if (count($matches)) {
            $result = call_user_func_array($this->getCallback(), $options);
        }
        return $result;
    }

    protected function addTickLimit(Query $query, $timeStart, $step)
    {
        $field = ($step < 1) ? 'id' : 'timestamp';
        $andNode = new LogicOperator\AndNode([
            $query->getQuery(),
            new ScalarOperator\GeNode($field, $timeStart),
            new ScalarOperator\LtNode($field, $timeStart + $step),
        ]);
        $query->setQuery($andNode);
        return $query;
    }
}
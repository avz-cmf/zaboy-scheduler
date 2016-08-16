<?php

namespace zaboy\scheduler\Task;

use Xiag\Rql\Parser\Query;
use zaboy\async\ClientAbstract;
use zaboy\scheduler\Task\Exception\TaskException;
use zaboy\scheduler\Task\Task\SimpleTask;

class Client extends ClientAbstract
{
    const EXCEPTION_CLASS = TaskException::class;

    /**
     * Returns the Task class.
     *
     * @param int|null $id
     * @return string
     */
    protected function getClass($id = null)
    {
        return SimpleTask::class;
    }

    /**
     * Creates a new Entity (Task) and sets it specified parameters.
     *
     * @param array|null $data
     * @return SimpleTask
     * @throws TaskException
     */
    protected function makeEntity($data = null)
    {
        $task = new SimpleTask($data);
        try {
            $rowsCount = $this->store->insert($task->getData());
        } catch (\Exception $e) {
            throw new TaskException('Can\'t insert taskData. Task: ' . $task->getId());
        }
        if (!$rowsCount) {
            throw new TaskException('Can\'t insert taskData. Task: ' . $task->getId());
        }
        return $task;
    }

    /**
     * Returns a raw data of Task
     *
     * @return array
     * @throws TaskException
     */
    public function toArray()
    {
        return $this->getStoredData($this->id);
    }

    /**
     * Makes check the task itself by timeline and execute callback if the time match to schedule
     *
     * @param $timeStart - the lower boundary of time
     * @param $step - length of time period
     * @param array $options - options for passing to callback
     *
     * @return mixed|null - returned value from callback
     * @throws TaskException
     */
    public function activate($timeStart, $step, $options = [])
    {
        /** @var SimpleTask $task */
        $task = $this->getTask();
        return $task->activate($timeStart, $step, $options);
    }


    /**
     * Sets a new value of schedule
     *
     * @param Query $query
     */
    public function setSchedule(Query $query)
    {
        $this->runTransaction('setSchedule', $query);
    }


    /**
     * Sets a new value of callback
     *
     * @param callable $callback
     */
    public function setCallback(callable $callback)
    {
        $this->runTransaction('setCallback', $callback);
    }


    /**
     * Sets a new value of field 'active'
     *
     * @param $active
     */
    public function setActive($active)
    {
        $this->runTransaction('setActive', $active);
    }


    /**
     * Returns a Task object by current id.
     *
     * @return SimpleTask
     * @throws TaskException
     */
    protected function getTask()
    {
        return new SimpleTask($this->getStoredData($this->id));
    }
}
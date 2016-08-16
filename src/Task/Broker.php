<?php

namespace zaboy\scheduler\Task;

class Broker
{
    /** @var Store $store */
    protected $store;

    /**
     * Broker constructor.
     *
     * @param Store $store
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    /**
     * Gets the task by specified id.
     *
     * @param $taskId
     * @return Client
     */
    public function getTask($taskId)
    {
        $task = new Client($this->store, $taskId);
        return $task;
    }

    /**
     * Creates new task and sets it specified parameters.
     *
     * The parameters 'schedule' and 'callback' are necessary
     *
     * @param $taskData
     * @return Client
     */
    public function makeTask($taskData)
    {
        $task = new Client($this->store, $taskData);
        return $task;
    }

    /**
     * Deactivate task.
     *
     * Sets the field 'active' to 'false'. The task is not deleted physically.
     *
     * @param $taskId
     */
    public function deleteTask($taskId)
    {
        $task = new Client($this->store, $taskId);
        $task->setActive(false);
    }



}
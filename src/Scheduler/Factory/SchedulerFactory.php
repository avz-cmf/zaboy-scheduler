<?php

namespace zaboy\scheduler\Scheduler\Factory;

use Interop\Container\ContainerInterface;
use zaboy\rest\DataStore\DataStoreAbstract;
use zaboy\rest\FactoryAbstract;
use zaboy\rest\DataStore\DataStoreException;
use zaboy\scheduler\Callback\CallbackManager;
use zaboy\scheduler\Scheduler\Scheduler;
use zaboy\scheduler\Scheduler\SchedulerException;

/**
 * Creates if can and returns an instance of class 'Scheduler'
 *
 * For correct work the config must contain part below (services names must be not changed!!)
 * <code>
 * 'factories' => [
 *     // ...
 *     'timeline_datastore' => 'zaboy\scheduler\DataStore\Factory\TimelineFactory',
 *     'filters_datastore' => 'zaboy\scheduler\DataStore\Factory\FilterDataStoreFactory',
 *
 *     // may absent; will be created from default class
 *     'callback_manager' => 'zaboy\scheduler\Callback\Factory\CallbackManagerFactory',
 * ]
 * </code>
 *
 * If you want to change the behavior by default you may describe the scheduler service like below:
 * <code>
 * // somewhere in config on the same level as 'services', 'dataStore', 'callback' etc.
 * 'scheduler' => [
 *     'filters_datastore' => 'real_service_name_for_filters_datastore',
 *     'timeline_datastore' => 'real_service_name_for_timeline_datastore,
 * ]
 * </code>
 *
 * Class ScriptAbstractFactory
 * @package zaboy\scheduler\Callback\Factory
 */
class SchedulerFactory extends FactoryAbstract
{
    const KEY_SCHEDULER = 'scheduler';

    const KEY_TASKS = 'tasks';

    const FILTERS_DATASTORE_SERVICE_NAME = 'filters_datastore';

    const TIMELINE_DATASTORE_SERVICE_NAME = 'timeline_datastore';

    /**
     * {@inherit}
     *
     * {@inherit}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var \zaboy\rest\DataStore\DataStoreAbstract $filterDs */
        $filterDs = $this->getServiceByName(
            $container,
            self::FILTERS_DATASTORE_SERVICE_NAME,
            Scheduler::DEFAULT_FILTERS_DATASTORE_SERVICE_NAME
        );
        if (!$filterDs->count()) {
            $this->fillTable($container, $filterDs);
        }

        /** @var \zaboy\scheduler\DataStore\Timeline $timelineDs */
        $timelineDs = $this->getServiceByName(
            $container,
            self::TIMELINE_DATASTORE_SERVICE_NAME,
            Scheduler::DEFAULT_TIMELINE_DATASTORE_SERVICE_NAME
        );

        // If callback manager is not described in config just creates it from constructor
        if ($container->has(CallbackManager::SERVICE_NAME)) {
            /** @var \zaboy\scheduler\Callback\CallbackManager $callbackManager */
            $callbackManager = $container->get(CallbackManager::SERVICE_NAME);
        } else {
            $callbackManager = new CallbackManager($container);
        }
        // Creates the Scheduler entity and injects to it necessary dependencies
        $instance = new Scheduler($filterDs, $timelineDs, $callbackManager);
        return $instance;
    }

    /**
     * Checks existing passed service name in the config and gets this entity from container
     *
     * @param ContainerInterface $container
     * @param $serviceName
     * @param $defaultServiceName
     * @return mixed
     * @throws SchedulerException
     */
    private function getServiceByName(ContainerInterface $container, $serviceName, $defaultServiceName)
    {
        $config = $container->get('config');
        // If specified service name does not exist gets the default service name
        if (isset($config[self::KEY_SCHEDULER][$serviceName])) {
            $serviceName = $config[self::KEY_SCHEDULER][$serviceName];
        } else {
            $serviceName = $defaultServiceName;
        }
        // Checks existing the service name gotten above
        if (!$container->has($serviceName)) {
            throw new SchedulerException("Can't create \"{$serviceName}\" because it's not described in config.");
        }
        $service = $container->get($serviceName);
        return $service;
    }

    /**
     * Fills table by data from config
     *
     * TODO: уйдет из этого класса, пока не известно, куда, но здесь этого кода быть не должно
     *
     * @param ContainerInterface $serviceLocator
     * @throws DataStoreException
     */
    protected function fillTable(ContainerInterface $container, DataStoreAbstract $dataStore)
    {
        $config = $container->get('config');
        // If configs for tasks doesn't exist do nothing
        if (!isset($config[self::KEY_TASKS])) {
            return;
        }
        $id = $dataStore->getIdentifier();
        foreach ($config[self::KEY_TASKS] as $task) {
            if (!isset($task[$id])) {
                throw new DataStoreException("Expected necessary parameter \"{$id}\" in data of filter");
            }
            $dataStore->create($task);
        }
    }
}
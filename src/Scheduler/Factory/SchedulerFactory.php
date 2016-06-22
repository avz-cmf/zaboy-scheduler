<?php

namespace zaboy\scheduler\Scheduler\Factory;

use Interop\Container\ContainerInterface;
use zaboy\scheduler\Callback\CallbackManager;
use zaboy\scheduler\FactoryAbstract;
use zaboy\scheduler\Scheduler\Scheduler;
use zaboy\scheduler\Scheduler\SchedulerException;

/**
 * Creates if can and returns an instance of class self::KEY_SCHEDULER
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

    /**
     * {@inherit}
     *
     * {@inherit}
     */
    public function __invoke(ContainerInterface $container)
    {
        $filterDs = $this->getServiceByName($container, Scheduler::FILTERS_DATASTORE_SERVICE_NAME);
        $timelineDs = $this->getServiceByName($container, Scheduler::TIMELINE_DATASTORE_SERVICE_NAME);

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
     * @return mixed
     * @throws SchedulerException
     */
    private function getServiceByName(ContainerInterface $container, $serviceName)
    {
        $config = $container->get('config');
        if (isset($config[self::KEY_SCHEDULER][$serviceName])) {
            $serviceName = $config[self::KEY_SCHEDULER][$serviceName];
        }
        if (!$container->has($serviceName)) {
            throw new SchedulerException("Can't create \"{$serviceName}\" because it's not described in config.");
        }
        $service = $container->get($serviceName);
        return $service;
    }
}
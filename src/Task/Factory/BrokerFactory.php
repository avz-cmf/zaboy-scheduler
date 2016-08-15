<?php

namespace zaboy\scheduler\Task\Factory;

use Interop\Container\ContainerInterface;
use zaboy\rest\FactoryAbstract;
use zaboy\scheduler\Task\Broker;

class BrokerFactory extends FactoryAbstract
{
    const KEY_TASK_BROKER = 'task_broker';

    const FILTERS_DATASTORE_SERVICE_NAME = 'filters_datastore';

    const KEY_TASKS = 'tasks';

    /**
     * {@inherit}
     *
     * {@inherit}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $filterDs = $container->get(StoreFactory::KEY);
        // Creates the Broker instance and injects to it the filter Store
        $instance = new Broker($filterDs);
        return $instance;
    }
}
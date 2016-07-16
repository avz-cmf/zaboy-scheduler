<?php

namespace zaboy\scheduler\Broker\Factory;

use Interop\Container\ContainerInterface;
use zaboy\rest\FactoryAbstract;
use zaboy\scheduler\Broker\ScriptBroker;
use zaboy\scheduler\Callback\CallbackException;
use Zend\Loader\Exception\InvalidArgumentException;

class ScriptBrokerFactory extends FactoryAbstract
{
    const KEY_COMMON_SERVICES = 'common_services';

    const KEY_DATASTORE = 'dataStore';

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ScriptBroker
     * @throws CallbackException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config')[self::KEY_COMMON_SERVICES];

        if (!isset($config[$requestedName])) {
            throw new InvalidArgumentException("The service with name \"{$requestedName}\" wasn't found");
        }

        $serviceConfig = $config[$requestedName];
        if (!isset($serviceConfig[self::KEY_DATASTORE])) {
            throw new CallbackException("The necessary parameter \"" . self::KEY_DATASTORE
                . "\" for initializing the callback service was not found");
        }
        if (!$container->has($serviceConfig[self::KEY_DATASTORE])) {
            throw new CallbackException("The service \"{$serviceConfig[self::KEY_DATASTORE]}\" for dataStore
                initializing was not described in config");
        }
        $dataStore = $container->get($serviceConfig[self::KEY_DATASTORE]);

        $parser = $container->get('parser');

        $instance = new ScriptBroker($dataStore, $parser);
        return $instance;
    }
}
<?php

namespace zaboy\scheduler\Broker\Factory;

use Interop\Container\ContainerInterface;
use zaboy\rest\FactoryAbstract;
use zaboy\scheduler\Broker\ScriptBroker;
use zaboy\scheduler\Callback\CallbackException;
use zaboy\async\Promise;

class ScriptBrokerFactory extends FactoryAbstract
{
    const KEY_COMMON_SERVICES = 'common_services';

    const KEY_DATASTORE = 'dataStore';

    const KEY_SCRIPT_BROKER = 'script_broker';

    /**
     * {@inherit}
     *
     * {@inherit}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config')[self::KEY_COMMON_SERVICES];

        $serviceConfig = $config[self::KEY_SCRIPT_BROKER];
        if (!isset($serviceConfig[self::KEY_DATASTORE])) {
            throw new CallbackException("The necessary parameter \"" . self::KEY_DATASTORE
                . "\" for initializing the callback service was not found");
        }
        if (!$container->has($serviceConfig[self::KEY_DATASTORE])) {
            throw new CallbackException("The service \"{$serviceConfig[self::KEY_DATASTORE]}\" for dataStore
                initializing was not described in config");
        }
        $dataStore = $container->get($serviceConfig[self::KEY_DATASTORE]);
        $parser = $container->get('error_parser');
        $promiseBroker = $container->get(Promise\Factory\BrokerFactory::KEY);

        $instance = new ScriptBroker($dataStore, $parser, $promiseBroker);
        return $instance;
    }
}
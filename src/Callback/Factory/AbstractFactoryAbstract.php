<?php

namespace zaboy\scheduler\Callback\Factory;

use Interop\Container\ContainerInterface;
use zaboy\scheduler\Callback\CallbackException;

/**
 * The abstract factory for all types of callbaks
 *
 * Class AbstractFactoryAbstract
 * @package zaboy\scheduler\Callback\Factory
 */
abstract class AbstractFactoryAbstract extends \zaboy\rest\AbstractFactoryAbstract
{
    const CLASS_IS_A = '';

    /**
     * {@inherit}
     *
     * {@inherit}
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $config = $container->get('config');
        if (!isset($config['callback'])) {
            return false;
        }
        $config = $config['callback'];
        if (!isset($config[$requestedName]['class'])) {
            return false;
        }
        $requestedClassName = $config[$requestedName]['class'];
        return is_a($requestedClassName, static::CLASS_IS_A, true);
    }

    /**
     * {@inherit}
     *
     * {@inherit}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        if (!isset($config['callback'])) {
            throw new CallbackException("The config hasn't the part \"callback\" in the application config.");
        }
        $config = $config['callback'];
        if (!isset($config[$requestedName])) {
            throw new CallbackException("The specified service name for callback \"{$requestedName}\" was not found");
        }
        $serviceConfig = $config[$requestedName];
        if (!isset($serviceConfig['class'])) {
            throw new CallbackException("Te necessary parameter \"class\" for initializing the callback service was not found");
        }
        $requestedClassName = $serviceConfig['class'];
        if (!isset($serviceConfig['params'])) {
            $params = [];
        } else {
            $params = $serviceConfig['params'];
        }
        return new $requestedClassName($params);
    }
}
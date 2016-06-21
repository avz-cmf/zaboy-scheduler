<?php

namespace zaboy\scheduler\Callback\Factory;

use Interop\Container\ContainerInterface;
use zaboy\scheduler\Callback\CallbackException;

/**
 * The abstract factory for all types of callbacks
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
        $this->checkNecessaryParametersInConfig($container, $requestedName);

        $config = $container->get('config')['callback'];
        $serviceConfig = $config[$requestedName];
        $requestedClassName = $serviceConfig['class'];
        if (!isset($serviceConfig['params'])) {
            $params = [];
        } else {
            $params = $serviceConfig['params'];
        }
        return new $requestedClassName($params);
    }

    /**
     * Checks existing of necessary parameters in
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @throws CallbackException
     */
    protected function checkNecessaryParametersInConfig(ContainerInterface $container, $requestedName)
    {
        $config = $container->get('config');
        if (!isset($config['callback'])) {
            throw new CallbackException("The config hasn't the part \"callback\" in the application config.");
        }
        if (!isset($config['callback'][$requestedName])) {
            throw new CallbackException("The specified service name for callback \"{$requestedName}\" was not found");
        }
        if (!isset($config['callback'][$requestedName]['class'])) {
            throw new CallbackException("Te necessary parameter \"class\" for initializing the callback service was not found");
        }
    }
}
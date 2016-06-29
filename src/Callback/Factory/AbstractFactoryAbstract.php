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

    const KEY_CALLBACK = 'callback';

    const KEY_PARAMS = 'params';

    /**
     * {@inherit}
     *
     * {@inherit}
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $config = $container->get('config');
        if (!isset($config[self::KEY_CALLBACK])) {
            return false;
        }
        $config = $config[self::KEY_CALLBACK];
        if (!isset($config[$requestedName][self::KEY_CLASS])) {
            return false;
        }
        $requestedClassName = $config[$requestedName][self::KEY_CLASS];
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

        $config = $container->get('config')[self::KEY_CALLBACK];
        $serviceConfig = $config[$requestedName];
        $requestedClassName = $serviceConfig[self::KEY_CLASS];
        if (!isset($serviceConfig[self::KEY_PARAMS])) {
            $params = [];
        } else {
            $params = $serviceConfig[self::KEY_PARAMS];
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
        if (!isset($config[self::KEY_CALLBACK])) {
            throw new CallbackException("The config hasn't the part \"callback\" in the application config.");
        }
        if (!isset($config[self::KEY_CALLBACK][$requestedName])) {
            throw new CallbackException("The specified service name for callback \"{$requestedName}\" was not found");
        }
        if (!isset($config[self::KEY_CALLBACK][$requestedName][self::KEY_CLASS])) {
            throw new CallbackException("The necessary parameter \"class\" for initializing the callback service was not found");
        }
    }
}
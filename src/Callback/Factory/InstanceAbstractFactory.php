<?php

namespace zaboy\scheduler\Callback\Factory;

use Interop\Container\ContainerInterface;

/**
 * Creates if can and returns an instance of class 'Callback\Instance'
 *
 * For correct work the config must contain part below:
 * <code>
 * 'callback' => [
 *     'real_service_name_for_this_callback_type' => [
 *         'class' => 'zaboy\scheduler\Callback\Instance',
 *         'params' => [
 *             'instanceServiceName' => 'service_name_for_instance_which_method_will_be_called',
 *             'instanceMethodName' => 'real_method_name_which_wil_be_called',
 *          ],
 *     ],
 * ]
 * </code>
 *
 * Class InstanceAbstractFactory
 * @package zaboy\scheduler\Callback\Factory
 */
class InstanceAbstractFactory extends AbstractFactoryAbstract
{
    const CLASS_IS_A = 'zaboy\scheduler\Callback\Instance';

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->checkNecessaryParametersInConfig($container, $requestedName);

        $config = $container->get('config')[self::KEY_CALLBACK];
        $serviceConfig = $config[$requestedName];
        // Class of callback object, will be 'zaboy\scheduler\Callback\Instance'
        $requestedClassName = $serviceConfig[self::KEY_CLASS];
        // The first parameter which the callback object gets is instance which method it calls
        $dependencyInstance = $container->get($serviceConfig[self::KEY_PARAMS]['instanceServiceName']);
        // Second parameter is name of method for call
        $methodName = $serviceConfig[self::KEY_PARAMS]['instanceMethodName'];

        $instance = new $requestedClassName([
            'instance' => $dependencyInstance,
            'method' => $methodName,
        ]);
        return $instance;
    }
}
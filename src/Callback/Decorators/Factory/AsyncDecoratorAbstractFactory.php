<?php

namespace zaboy\scheduler\Callback\Decorators\Factory;

use Interop\Container\ContainerInterface;
use zaboy\scheduler\Callback\CallbackException;
use zaboy\scheduler\Callback\Factory\AbstractFactoryAbstract;

class AsyncDecoratorAbstractFactory extends AbstractFactoryAbstract
{
    const CLASS_IS_A = 'zaboy\scheduler\Callback\Decorators\AsyncDecorator';

    const KEY_RPC_CALLBACK = 'rpc_callback';

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->checkNecessaryParametersInConfig($container, $requestedName);

        $config = $container->get('config')[self::KEY_CALLBACK];
        $serviceConfig = $config[$requestedName];
        $requestedClassName = $serviceConfig[self::KEY_CLASS];

        if (!isset($serviceConfig[self::KEY_RPC_CALLBACK])) {
            throw new CallbackException("The necessary parameter for decorator \"rpc_callback\" was not found");
        }
        $callbackServiceName = $serviceConfig[self::KEY_RPC_CALLBACK];

        return new $requestedClassName($callbackServiceName);
    }
}
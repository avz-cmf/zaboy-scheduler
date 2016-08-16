<?php

namespace zaboy\scheduler\Callback\Decorators\Factory;

use Interop\Container\ContainerInterface;
use zaboy\rest\FactoryAbstract;
use zaboy\scheduler\Callback\Decorators\ScriptDecorator;

class ScriptDecoratorAbstractFactory extends FactoryAbstract
{
    /**
     * {@inherit}
     *
     * {@inherit}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $scriptBroker = $container->get('script_broker');

        $instance = new ScriptDecorator($scriptBroker);
        return $instance;
    }
}
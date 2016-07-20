<?php

namespace zaboy\test\scheduler\Callback\Factory;

use Interop\Container\ContainerInterface;
use zaboy\rest\FactoryAbstract;
use zaboy\test\scheduler\Examples\Callback\SimpleClass;

class SimpleClassFactory extends FactoryAbstract
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SimpleClass($container);
    }

}
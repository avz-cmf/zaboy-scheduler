<?php

namespace zaboy\scheduler\FileSystem\Factory;

use Interop\Container\ContainerInterface;
use zaboy\rest\FactoryAbstract;
use zaboy\scheduler\FileSystem\FileManagerCsv;
use Zend\ServiceManager\ServiceLocatorInterface;

class FileManagerCsvFactory extends FactoryAbstract
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new FileManagerCsv();
    }

}
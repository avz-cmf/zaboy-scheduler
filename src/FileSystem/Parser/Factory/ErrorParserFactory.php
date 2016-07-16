<?php

namespace zaboy\scheduler\FileSystem\Parser\Factory;

use Interop\Container\ContainerInterface;
use zaboy\rest\FactoryAbstract;
use zaboy\scheduler\FileSystem\Parser\ErrorParser;
use Zend\Loader\Exception\InvalidArgumentException;

class ErrorParserFactory extends FactoryAbstract
{
    const KEY_COMMON_SERVICES = 'common_services';

    const KEY_PATTERNS = 'patterns';

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config')[self::KEY_COMMON_SERVICES];

        if (!isset($config[$requestedName])) {
            throw new InvalidArgumentException("The service with name \"{$requestedName}\" wasn't found");
        }

        $serviceConfig = $config[$requestedName];
        $patterns = $serviceConfig[self::KEY_PATTERNS];

        $parser = new ErrorParser($patterns);
        return $parser;
    }
}
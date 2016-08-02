<?php

namespace zaboy\scheduler\FileSystem\Parser\Factory;

use Interop\Container\ContainerInterface;
use zaboy\rest\FactoryAbstract;
use zaboy\scheduler\FileSystem\Parser\OutputParser;
use Zend\Loader\Exception\InvalidArgumentException;

class ErrorParserFactory extends FactoryAbstract
{
    const KEY_COMMON_SERVICES = 'common_services';

    const KEY_PATTERNS = 'patterns';

    const KEY_ERROR_PARSER = 'error_parser';

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config')[self::KEY_COMMON_SERVICES];

        $serviceConfig = $config[self::KEY_ERROR_PARSER];
        $patterns = $serviceConfig[self::KEY_PATTERNS];

        $parser = new OutputParser($patterns);
        return $parser;
    }
}
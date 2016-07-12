<?php

namespace zaboy\scheduler\Callback\Factory;
use Interop\Container\ContainerInterface;
use zaboy\rest\Interop;
use zaboy\scheduler\Callback\CallbackException;

/**
 * Creates if can and returns an instance of class 'Callback\StaticMethod'
 *
 * For correct work the config must contain part below:
 * <code>
 * 'callback' => [
 *     'real_service_name' => [
 *         'class' => 'zaboy\scheduler\Callback\StaticMethod',
 *         'params' => [
 *             'method' => '\Real\Class\Name\Or\Namespace::RealMethodName',
 *             // OR
 *             'method' => ['\Real\Class\Name\Or\Namespace', 'RealMethodName'],
 *          ],
 *     ],
 * ]
 * </code>
 *
 * Class StaticMethodAbstractFactory
 * @package zaboy\scheduler\Callback\Factory
 */
class StaticMethodAbstarctFactory extends AbstractFactoryAbstract
{
    const CLASS_IS_A = 'zaboy\scheduler\Callback\StaticMethod';

    const KEY_METHOD = 'method';

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

        if (!isset($serviceConfig[self::KEY_METHOD])) {
            throw new CallbackException("It's expected the necessary parameter \""
                . self::KEY_METHOD . "\" in the config");
        }

        $method = $serviceConfig[self::KEY_METHOD];

        return new $requestedClassName($method);
    }
}
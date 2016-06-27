<?php

namespace zaboy\scheduler\Callback\Factory;

use Interop\Container\ContainerInterface;
use zaboy\scheduler\Callback\CallbackException;

/**
 * Creates if can and returns an instance of class 'Callback\Worker'
 *
 * Worker it is child of the ScriptCallback. It allows to run the php-script in the background and gets back the PID
 * of the run process.
 *
 * For correct work the config must contain part below:
 * <code>
 * 'callback' => [
 *     'real_service_name_of_the_worker_callback' => [
 *         'class' => 'zaboy\scheduler\Callback\Worker',
 *         'params' => [
 *             'script_name' => 'real/script/name.php',
 *             'dataStore' => 'real_service_name_of_datastore_for_saving_the_PIDs',
 *         ],
 *     ],
 * ],
 * </code>
 *
 * @see \zaboy\scheduler\Callback\Worker
 *
 * Class WorkerAbstractFactory
 * @package zaboy\scheduler\Callback\Factory
 */
class WorkerAbstractFactory extends AbstractFactoryAbstract
{
    const CLASS_IS_A = 'zaboy\scheduler\Callback\Worker';

    /**
     * {@inherit}
     *
     * {@inherit}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->checkNecessaryParametersInConfig($container, $requestedName);

        $serviceConfig = $container->get('config')[self::KEY_CALLBACK][$requestedName];
        if (!isset($serviceConfig[self::KEY_PARAMS]['dataStore'])) {
            throw new CallbackException("The necessary parameter \"data_store\" for initializing the callback service was not found");
        }
        $requestedClassName = $serviceConfig[self::KEY_CLASS];

        $callbackParams = $serviceConfig[self::KEY_PARAMS];

        $callbackParams['dataStore'] = $container->get($serviceConfig[self::KEY_PARAMS]['dataStore']);

        $instance = new $requestedClassName($callbackParams);
        return $instance;
    }
}
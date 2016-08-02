<?php

namespace zaboy\scheduler\Callback\Factory;
use Interop\Container\ContainerInterface;
use zaboy\scheduler\Callback\CallbackException;
use zaboy\scheduler\Callback\Script;
use zaboy\scheduler\FileSystem\CommandLineWorker;
use zaboy\scheduler\FileSystem\ScriptWorker;

/**
 * Creates if can and returns an instance of class 'Callback\Script'
 *
 * Script callback allows to run any script in the system and to read its output.
 *
 * For correct work the config must contain part below:
 * <code>
 * 'callback' => [
 *     'real_service_name' => [
 *         'class' => 'zaboy\scheduler\Callback\Script',
*          'script_name' => 'real/script/name.php',
 *
 *         // this parameter is not necessary; can be: '', 'php', 'phyton' etc. or absent at all
 *         'command_prefix' => 'php',
 *     ],
 * ]
 * </code>
 *
 * Class ScriptAbstractFactory
 * @package zaboy\scheduler\Callback\Factory
 */
class ScriptAbstractFactory extends AbstractFactoryAbstract
{
    const CLASS_IS_A = 'zaboy\scheduler\Callback\Script';

    const KEY_SCRIPT_NAME = 'script_name';

    const KEY_COMMAND_PREFIX = 'command_prefix';

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->checkNecessaryParametersInConfig($container, $requestedName);

        $config = $container->get('config')[self::KEY_CALLBACK];
        $serviceConfig = $config[$requestedName];
        $requestedClassName = $serviceConfig[self::KEY_CLASS];

        if (!isset($serviceConfig[self::KEY_SCRIPT_NAME])) {
            throw new CallbackException("It's expected the necessary parameter \""
                . self::KEY_SCRIPT_NAME . "\" in the config");
        }
        $scriptName = $serviceConfig[self::KEY_SCRIPT_NAME];

        $commandPrefix = (isset($serviceConfig[self::KEY_COMMAND_PREFIX]) ?: Script::DEFAULT_COMMAND_PREFIX);
        $commandLineWorker = new CommandLineWorker();
        $parser = $container->get('error_parser');

        return new $requestedClassName($scriptName, $commandPrefix, $commandLineWorker, $parser);
    }


}
<?php

namespace zaboy\scheduler\Callback\Factory;

/**
 * Creates if can and returns an instance of class 'Callback\ScriptProxy'
 *
 * For correct work the config must contain part below:
 * <code>
 * 'callback' => [
 *     'real_service_name' => [
 *         'class' => 'zaboy\scheduler\Callback\ScriptProxy',
 *         'params' => [
 *             'rpc_callback' => 'real_service_name_for_callback',
 *          ],
 *     ],
 * ]
 * </code>
 *
 * Class ScriptProxyAbstractFactory
 * @package zaboy\scheduler\Callback\Factory
 */
class ScriptProxyAbstractFactory extends AbstractFactoryAbstract
{
    const CLASS_IS_A = 'zaboy\scheduler\Callback\ScriptProxy';
}
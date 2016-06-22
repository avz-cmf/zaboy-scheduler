<?php

namespace zaboy\scheduler\Ticker\Factory;

use Interop\Container\ContainerInterface;
use zaboy\rest\FactoryAbstract;
use zaboy\scheduler\Callback\CallbackException;
use zaboy\scheduler\Callback\CallbackManager;
use zaboy\scheduler\Ticker\Ticker;

/**
 * Creates if can and returns an instance of class 'Ticker'
 *
 * For correct work the config must contain part below:
 * <code>
 * 'real_name_for_ticker_service' => [
 *     'total_time' => 60,  // not necessary
 *     'step' => 1,         // not necessary
 *     'hop' => [
 *         'callback' => 'real_service_name_for_callback_type',
 *     ],
 *     'tick' => [
 *         'callback' => 'real_service_name_for_callback_type',
 *     ],
 * ],
 * </code>
 *
 * Class ScriptAbstractFactory
 * @package zaboy\scheduler\Callback\Factory
 */
class TickerFactory extends FactoryAbstract
{
    const TICKER_SERVICE_NAME = 'ticker';

    /**
     * {@inherit}
     *
     * {@inherit}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config')[self::TICKER_SERVICE_NAME];

        $cm = new CallbackManager($container);
        $cm->get('');

        if (!isset($config['hop']['callback'])) {
            throw new CallbackException("The necessary parameter \"hop/callback\" does not exist.");
        }
        if (!isset($config['tick']['callback'])) {
            throw new CallbackException("The necessary parameter \"tick/callback\" does not exist.");
        }
        /** @var \zaboy\scheduler\Callback\Interfaces\CallbackInterface $hopCallback */
        $hopCallback = $container->get($config['hop']['callback']);
        /** @var \zaboy\scheduler\Callback\Interfaces\CallbackInterface $tickCallback */
        $tickCallback = $container->get($config['tick']['callback']);

        $ticker = new Ticker($tickCallback, $hopCallback, $config);
        return $ticker;
    }
}
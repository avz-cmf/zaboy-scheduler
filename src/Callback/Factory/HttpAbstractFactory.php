<?php

namespace zaboy\scheduler\Callback\Factory;

/**
 * Creates if can and returns an instance of class 'Callback\Http'
 *
 * For correct work the config must contain part below:
 * <code>
 * 'callback' => [
 *     'real_service_name_for_this_callback_type' => [
 *         'class' => 'zaboy\scheduler\Callback\Http',
 *         'params' => [
 *             'url' => 'http://real.url.to.request.sending',
 *             // various options for request, for example:
 *             'requestOptions' => [
 *                 'timeout' => 30,
 *             ],
 *             // Method for request; for example:
 *             'method' => \Zend\Http\Request::METHOD_GET,
 *             // Various headers for request, for example:
 *             'headers' => [
 *                 'Content-Type' => 'application/json',
 *                 'Accept' => 'application/json',
 *             ],
 *          ],
 *     ],
 * ]
 * </code>
 *
 * Class HttpAbstractFactory
 * @package zaboy\scheduler\Callback\Factory
 */
class HttpAbstractFactory extends AbstractFactoryAbstract
{
    const CLASS_IS_A = 'zaboy\scheduler\Callback\Http';
}
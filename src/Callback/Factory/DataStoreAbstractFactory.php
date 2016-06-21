<?php

namespace zaboy\scheduler\Callback\Factory;

use Interop\Container\ContainerInterface;
use zaboy\scheduler\Callback\CallbackException;

/**
 * Creates if can and returns an instance of class 'Callback\DataStore'
 *
 * For correct work the config must contain part below:
 * <code>
 * 'callback' => [
 *     'real_service_name_for_this_callback_type' => [
 *         'class' => 'zaboy\scheduler\Callback\DataStore',
 *         'params' => [
 *             'data_store' => 'data_store_service_name_which_will_be_created',
 *          ],
 *     ],
 * ]
 * </code>
 *
 * There is a number of options that should be necessary passed to method "call":
 * - `method`; required; it's the method of DataStore that will be done;
 *    the allowed values are:
 *       `read`, `create`, `update`, `delete`, `deleteAll`, `query`;
 *    the symbol register not important
 *
 * - `flags`; not required; an array;
 *       it contents additional flags for some methods like `create` and `update`;
 *       for `create` methods the `rewriteIfExist` flag is necessary;
 *       for `update` methods the `createIfAbsent` flag is necessary;
 *       False is value for both flags by default.
 *       If this key specified for method that not requires it will be ignored.
 *
 * - `item_data`; required except for `deleteAll`;
 *       this parameter is an array which contents all necessary data for correct working each of method above.
 *       The methods `read` and `delete` need one parameter: id.
 *       The method `query` needs rql-query only
 *       etc.

 *
 * Examples:
 * 1. Read
 * <code>
 *    $callback->call([
 *        'method' => 'read',
 *        'item_data' => [
 *            'id' => 1
 *        ],
 *    ]);
 * </code>
 *
 * 2. Create without id
 * <code>
 *    $callback->call([
 *        'method' => 'create',
 *        'item_data' => [
 *            'key1' => 'val1',
 *            'key2' => 'val2',
 *            // ...
 *            'keyN' => 'valN',
 *        ],
 *        // 'flags' => [] // not required
 *    ]);
 * </code>
 *
 * 3. Create with id
 * <code>
 *    $callback->call([
 *        'method' => 'create',
 *        'item_data' => [
 *            'id' => 1
 *            'key1' => 'val1',
 *            'key2' => 'val2',
 *            // ...
 *            'keyN' => 'valN',
 *        ],
 *        'flags' => [
 *            'rewriteIfExist' => true/false
 *        ]
 *    ]);
 * </code>
 *
 * 4. Update
 * <code>
 *    $callback->call([
 *        'method' => 'update',
 *        'item_data' => [
 *            'id' => 1
 *            'key1' => 'val1',
 *            'key2' => 'val2',
 *            // ...
 *            'keyN' => 'valN',
 *        ],
 *        'flags' => [
 *            'createIfAbsent' => true/false
 *        ]
 *    ]);
 * </code>
 *
 * 5. Delete
 * <code>
 *    $callback->call([
 *        'method' => 'delete',
 *        'item_data' => [
 *            'id' => 1
 *        ],
 *    ]);
 * </code>
 *
 * 6. DeleteAll
 * <code>
 *    $callback->call([
 *        'method' => 'deleteAll',
 *        // 'item_data' => [], // not required
 *    ]);
 * </code>
 *
 * 7. Query
 * <code>
 *    $callback->call([
 *        'method' => 'query',
 *        'item_data' => [
 *            'query' => 'rql-expression'
 *        ],
 *    ]);
 * </code>
 *
 *
 * Class DataStoreAbstractFactory
 * @package zaboy\scheduler\Callback\Factory
 */
class DataStoreAbstractFactory extends AbstractFactoryAbstract
{
    const CLASS_IS_A = 'zaboy\scheduler\Callback\DataStore';

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->checkNecessaryParametersInConfig($container, $requestedName);

        $config = $container->get('config')['callback'];
        $serviceConfig = $config[$requestedName];
        // Class of callback object, will be 'zaboy\scheduler\Callback\Instance'
        $requestedClassName = $serviceConfig['class'];

        // The parameter which the callback object gets is instance of DataStore
        $dataStore = $container->get($serviceConfig['params']['data_store']);

        $instance = new $requestedClassName([
            'data_store' => $dataStore,
        ]);
        return $instance;
    }

    protected function checkNecessaryParametersInConfig(ContainerInterface $container, $requestedName)
    {
        parent::checkNecessaryParametersInConfig($container, $requestedName);

        $config = $container->get('config')['callback'];
        $serviceConfig = $config[$requestedName];
        if (!isset($serviceConfig['params']['data_store'])) {
            throw new CallbackException("The necessary parameter \"params/data_store\" for initializing the callback
                service was not found");
        }
        if (!$container->has($serviceConfig['params']['data_store'])) {
            throw new CallbackException("The service \"{$serviceConfig['params']['data_store']}\" for datastore
                initializing was not described in config");
        }
    }
}
<?php

namespace zaboy\scheduler\DataStore\Factory;

use Interop\Container\ContainerInterface;
use zaboy\rest\DataStore\DataStoreException;
use zaboy\rest\DataStore\DbTable;
use zaboy\rest\TableGateway\TableManagerMysql;
use Zend\Db\TableGateway\TableGateway;
use \zaboy\rest\FactoryAbstract;

/**
 * Creates if can and returns an instance of class DataStore 'DbTable'
 *
 * Also checks existing a table for this DataStore. If it does not exist creates one.
 * The table name must be 'filters'.
 *
 * The service must be described in 'factories' part of config:
 * 'factories' => [
 *     // ...
 *     'real_name_for_filters_datastore' => 'zaboy\scheduler\DataStore\Factory\FilterDataStoreFactory',
 * ]
 *
 * If you want to fill the table from config, it must have the architecture below (for example):
 *
 * <code>
 * 'tasks' => [
 *     'real_name_for_task_fe_task1' => [
 *         'id' => 1,
 *         'rql' => 'some rql-query expression',
 *         'callback' => 'real_service_name_for_callback',
 *         'active' => 1, // or 0 - for deactivate
 *     ],
 *     // ...
 *     'real_name_for_task_fe_taskN' => [
 *         'id' => 1,
 *         'rql' => 'some rql-query expression',
 *         'callback' => 'real_service_name_for_callback',
 *         'active' => 1, // or 0 - for deactivate
 *     ],
 * ]
 * </code>
 *
 * Class ScriptAbstractFactory
 * @package zaboy\scheduler\Callback\Factory
 */
class FilterDataStoreFactory extends FactoryAbstract
{
    const TABLE_NAME = 'filters';


    /** @var \Zend\Db\Adapter\Adapter $db */
    protected $db;

    protected $tableManageMysql;

    /** @var  \zaboy\rest\DataStore\DbTable */
    protected $dataStore;

    protected $tableConfig = [
        TableManagerMysql::KEY_TABLES_CONFIGS => [
            self::TABLE_NAME => [
                'id' => [
                    'fild_type' => 'Integer',
                    'fild_params' => [
                        'options' => [
                            'autoincrement' => true
                        ],
                    ],
                ],
                'rql' => [
                    'fild_type' => 'Text',
                    'fild_params' => [],
                ],
                'callback' => [
                    'fild_type' => 'Varchar',
                    'fild_params' => [
                        'length' => 255,
                        'nullable' => false,
                    ],
                ],
                'active' => [
                    'fild_type' => 'Boolean',
                    'fild_params' => [
                        'default' => true
                    ],
                ],
            ],
        ],
    ];

    /**
     * {@inherit}
     *
     * {@inherit}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->db = $container->has('db') ? $container->get('db') : null;
        if (is_null($this->db)) {
            throw new DataStoreException(
                'Can\'t create Zend\Db\TableGateway\TableGateway for ' . self::TABLE_NAME
            );
        }

        $tableManager = new TableManagerMysql($this->db, $this->tableConfig);

//        $hasTable = $tableManager->hasTable(self::TABLE_NAME);
//        if (!$hasTable) {
        if (!$tableManager->hasTable(self::TABLE_NAME)) {
            $tableManager->createTable(self::TABLE_NAME, self::TABLE_NAME);
        }

        $tableGateway = new TableGateway(self::TABLE_NAME, $this->db);
        $this->dataStore = new DbTable($tableGateway);
//        // Fill table using DbTable DataStore interface
//        if (!$hasTable) {
//            $this->fillTable($container);
//        }
        return $this->dataStore;
    }
}
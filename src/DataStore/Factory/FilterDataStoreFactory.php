<?php

namespace zaboy\scheduler\DataStore\Factory;

use Interop\Container\ContainerInterface;
use zaboy\rest\DataStore\DataStoreException;
use zaboy\rest\DataStore\DbTable;
use zaboy\scheduler\FactoryAbstract;
use Zend\Db\Metadata;
use Zend\Db\Sql\Ddl\CreateTable;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Ddl\Column;
use Zend\Db\Sql\Ddl\Constraint;
use Zend\Db\Sql\Sql;

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

    const KEY_TASKS = 'tasks';

    /** @var \Zend\Db\Adapter\Adapter $db */
    protected $db;

    /** @var  \zaboy\rest\DataStore\DbTable */
    protected $dataStore;

    /**
     * {@inherit}
     *
     * {@inherit}
     */
    public function __invoke(ContainerInterface $container)
    {
        $this->db = $container->has('db') ? $container->get('db') : null;
        if (is_null($this->db)) {
            throw new DataStoreException(
                'Can\'t create Zend\Db\TableGateway\TableGateway for ' . self::TABLE_NAME
            );
        }

        $hasTable = $this->hasTable();
        if (!$hasTable) {
            $this->createTable($container);
        }

        $tableGateway = new TableGateway(self::TABLE_NAME, $this->db);
        $this->dataStore = new DbTable($tableGateway);
        // Fill table using DbTable DataStore interface
        if (!$hasTable) {
            $this->fillTable($container);
        }
        return $this->dataStore;
    }

    /**
     * Checks if table exists
     *
     * @return bool
     */
    protected function hasTable()
    {
        $dbMetadata = Metadata\Source\Factory::createSourceFromAdapter($this->db);
        $tableNames = $dbMetadata->getTableNames();
        if (in_array(self::TABLE_NAME, $tableNames)) {
            return true;
        }
        return false;
    }

    /**
     * Creates the table
     *
     * @param ContainerInterface $serviceLocator
     */
    protected function createTable(ContainerInterface $container)
    {
        $table = new CreateTable(self::TABLE_NAME);

        $table->addColumn(new Column\Varchar('id', 255));
        $table->addColumn(new Column\Text('rql'));
        $table->addColumn(new Column\Varchar('callback', 255));
        $table->addColumn(new Column\Boolean('active', true, true));

        $table->addConstraint(new Constraint\PrimaryKey('id'));

        // existence of $adapter is assumed
        $sql = new Sql($this->db);
        $this->db->query(
            $sql->buildSqlString($table),
            \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE
        );
    }

    /**
     * Fills table by data from config
     *
     * @param ContainerInterface $serviceLocator
     * @throws DataStoreException
     */
    protected function fillTable(ContainerInterface $container)
    {
        $config = $container->get('config');
        // If configs for tasks doesn't exist do nothing
        if (!isset($config[self::KEY_TASKS])) {
            return;
        }
        $id = $this->dataStore->getIdentifier();
        foreach ($config[self::KEY_TASKS] as $task) {
            if (!isset($task[$id])) {
                throw new DataStoreException("Expected necessary parameter \"{$id}\" in data of filter");
            }
            $this->dataStore->create($task);
        }
    }
}
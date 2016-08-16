<?php

namespace zaboy\scheduler\Task\Factory;

use Interop\Container\ContainerInterface;
use zaboy\rest\FactoryAbstract;
use zaboy\rest\TableGateway\TableManagerMysql;
use zaboy\scheduler\Task\Store;
use Zend\Db\Exception\UnexpectedValueException;

class StoreFactory extends FactoryAbstract
{
    const KEY = '#Tasks Store';

    const TABLE_NAME = 'filtermanager';

    const KEY_TABLE_NAME = '#table-name';

    protected $taskStoreData = [
        Store::ID => [
            'field_type' => 'Varchar',
            'field_params' => [
                'length' => 255,
                'nullable' => false,
            ],
        ],
        Store::CREATION_TIME => [
            'field_type' => 'Integer',
            'field_params' => [
                'nullable' => false
            ]
        ],
        Store::SCHEDULE => [
            'field_type' => 'Text',
            'field_params' => [
                'nullable' => true,
            ],
        ],
        Store::CALLBACK => [
            'field_type' => 'Varchar',
            'field_params' => [
                'length' => 255,
                'nullable' => true,
            ],
        ],
        Store::ACTIVE => [
            'field_type' => 'Boolean',
            'field_params' => [
                'default' => true,
                'nullable' => true,
            ],
        ],
    ];


    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');

        $tableName = isset($config[self::KEY][self::KEY_TABLE_NAME]) ?
            $config[self::KEY][self::KEY_TABLE_NAME] :
            (isset($options[self::KEY_TABLE_NAME]) ?
                $options[self::KEY_TABLE_NAME] :
                self::TABLE_NAME)
        ;

        $db = $container->has('db') ? $container->get('db') : null;
        if (is_null($db)) {
            throw new UnexpectedValueException(
                'Can\'t create db Adapter'
            );
        }
        if ($container->has(TableManagerMysql::KEY_IN_CONFIG)) {
            $tableManager = $container->get(TableManagerMysql::KEY_IN_CONFIG);
        } else {
            $tableManager = new TableManagerMysql($db);
        }

        $hasPromiseStoreTable = $tableManager->hasTable($tableName);
        if (!$hasPromiseStoreTable) {
            $tableManager->rewriteTable($tableName, $this->taskStoreData);
        }

        return new Store($tableName, $db);
    }

}
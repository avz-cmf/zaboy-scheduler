<?php

use \zaboy\scheduler\Task;

return [
    'services' => [
        'factories' => [
            'task_broker' => Task\Factory\BrokerFactory::class,
            Task\Factory\StoreFactory::KEY => Task\Factory\StoreFactory::class,
        ],
    ],

    'tableManagerMysql' => [
        'tablesConfigs' => [
            'table_config' => [],
        ],
        'autocreateTables' => [
            'filtermanager' => 'table_config'
        ]
    ],

    Task\Factory\StoreFactory::KEY => [
        Task\Factory\StoreFactory::KEY_TABLE_NAME => Task\Factory\StoreFactory::TABLE_NAME,
    ],
];
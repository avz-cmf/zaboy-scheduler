<?php

return [

    'services' => [
        'invokables' => [
        ],
        'factories' => [
            'timeline_datastore' => 'zaboy\scheduler\DataStore\Factory\TimelineFactory',
            'ticker' => 'zaboy\scheduler\Ticker\Factory\TickerFactory',
            'scheduler' => 'zaboy\scheduler\Scheduler\Factory\SchedulerFactory',
            'filters_datastore' => 'zaboy\scheduler\DataStore\Factory\FilterDataStoreFactory',
            'callback_manager' => 'zaboy\scheduler\Callback\Factory\CallbackManagerFactory',
            'script_broker' => 'zaboy\scheduler\Broker\Factory\ScriptBrokerFactory',
            'error_parser' => 'zaboy\scheduler\FileSystem\Parser\Factory\ErrorParserFactory',
            'Store' => 'zaboy\async\Promise\Factory\StoreFactory',
        ],
        'abstract_factories' => [
            'zaboy\rest\DataStore\Factory\CsvAbstractFactory',
            'zaboy\scheduler\Callback\Factory\ScriptAbstractFactory',
//            'zaboy\scheduler\Callback\Factory\ScriptProxyAbstractFactory',
            'zaboy\scheduler\Callback\Factory\StaticMethodAbstarctFactory',
            'zaboy\scheduler\Callback\Factory\InstanceAbstractFactory',
            'Zend\Db\Adapter\AdapterAbstractServiceFactory',
        ]
    ],
];

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
        ],
        'abstract_factories' => [
            'zaboy\rest\DataStore\Factory\CsvAbstractFactory',
            'zaboy\scheduler\Callback\Factory\ScriptAbstractFactory',
            'zaboy\scheduler\Callback\Factory\ScriptProxyAbstractFactory',
            'zaboy\scheduler\Callback\Factory\StaticMethodAbstarctFactory',
            'zaboy\scheduler\Callback\Factory\InstanceAbstractFactory',
            'Zend\Db\Adapter\AdapterAbstractServiceFactory',
        ]
    ],
];

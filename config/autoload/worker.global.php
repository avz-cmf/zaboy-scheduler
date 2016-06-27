<?php

return [
    'services' => [
        'abstract_factories' => [
            'zaboy\scheduler\Callback\Factory\WorkerAbstractFactory',
        ],
    ],
    'callback' => [
        'worker_example_callback' => [
            'class' => 'zaboy\scheduler\Callback\Worker',
            'params' => [
                'script_name' => getcwd() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Callback'
                    . DIRECTORY_SEPARATOR . 'Examples' . DIRECTORY_SEPARATOR . 'testWorkerCallback.php',
                'dataStore' => 'pids_datastore',
            ],
        ],
    ],
    'dataStore' => [
        'pids_datastore' => [
            'class' => 'zaboy\rest\DataStore\CsvBase',
            'filename' => getcwd() . '/test/logs/pids.log',
            'delimiter' => ';',
            'fileConfig' => [
                'id',
                'pid',
                'startedAt',
                'scriptName',
                'timeout'
            ],
        ],
    ],
];
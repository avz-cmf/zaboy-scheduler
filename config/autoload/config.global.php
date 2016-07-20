<?php

return [
    'dataStore' => [
        'tick_log_datastore' => [
            'class' => 'zaboy\rest\DataStore\CsvIntId',
            'filename' => getcwd() . '/test/logs/tick.log',
            'delimiter' => ';',
        ],
        'hop_log_datastore' => [
            'class' => 'zaboy\rest\DataStore\CsvIntId',
            'filename' => getcwd() . '/test/logs/hop.log',
            'delimiter' => ';',
        ],
    ],
    'ticker' => [
        'total_time' => 30,
        'step' => 1,
        'hop' => [
            'callback' => 'hop_callback',
            'callbackParams' => [
                'max_log_rows' => 600,
            ],
        ],
        'tick' => [
            'callback' => 'tick_callback',
            'callbackParams' => [
                'max_log_rows' => 600,
            ],
        ],
    ],
    'callback' => [
        'hop_callback' => [
            'class' => 'zaboy\scheduler\Callback\Script',
            'script_name' => 'scripts/hop.php',
        ],
        'tick_callback' => [
            'class' => 'zaboy\scheduler\Callback\Script',
            'script_name' => 'scripts/tick.php',
        ],
        'schedule_callback' => [
            'class' => 'zaboy\scheduler\Callback\Instance',
            'params' => [
                'instanceServiceName' => 'scheduler',
                'instanceMethodName' => 'callback',
            ],
        ],
        'async_decorator' => [
            'class' => '\zaboy\scheduler\Callback\Decorators\ScriptDecorator',
            'callback' => 'tick_callback',
        ],
    ],

    'common_services' => [
        'script_broker' => [
            'dataStore' => 'pids_datastore',
        ],

        'error_parser' => [
            'patterns' => [
                'php' => "/^(PHP\s)?(Fatal|Parse) error:/",
                'phyton' => "/^Traceback \(most recent call last\):/",
            ],
        ],
    ],
];
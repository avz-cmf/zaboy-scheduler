<?php

return [
    'dataStore' => [
        'test_scheduler_filters_datastore' => [
            'class' => 'zaboy\rest\DataStore\Memory',
        ],
    ],
    'test_ticker_script_callback' => [
        'total_time' => 30,
        'step' => 1,
        'hop' => [
            'callback' => 'script_hop_callback',
            'callbackParams' => [
                'max_log_rows' => 600,
            ],
        ],
        'tick' => [
            'callback' => 'script_tick_callback',
            'callbackParams' => [
                'max_log_rows' => 600,
            ],
        ],
    ],

    'test_ticker_staticmethod_callback' => [
        'total_time' => 30,
        'step' => 1,
        'hop' => [
            'callback' => 'staticmethod_hop_callback',
            'callbackParams' => [
                'max_log_rows' => 600,
            ],
        ],
        'tick' => [
            'callback' => 'staticmethod_tick_callback',
            'callbackParams' => [
                'max_log_rows' => 600,
            ],
        ],
    ],

    'test_schedule_callback' => [
        'total_time' => 60,
        'step' => 1,
        'hop' => [
            'callback' => 'scheduler_hop_callback',
        ],
        'tick' => [
            'callback' => 'scheduler_tick_callback',
        ],
    ],

    'callback' => [
        'script_hop_callback' => [
            'class' => 'zaboy\scheduler\Callback\Script',
            'params' => [
                'script_name' => 'scripts/hop.php',
            ],
        ],
        'script_tick_callback' => [
            'class' => 'zaboy\scheduler\Callback\Script',
            'params' => [
                'script_name' => 'scripts/tick.php',
            ],
        ],
        'staticmethod_hop_callback' => [
            'class' => 'zaboy\scheduler\Callback\StaticMethod',
            'params' => [
                'method' => 'zaboy\test\scheduler\Ticker\TickerStaticMethodCallbackTest::methodForHopCallback',
            ],
        ],
        'staticmethod_tick_callback' => [
            'class' => 'zaboy\scheduler\Callback\StaticMethod',
            'params' => [
                'method' => ['zaboy\test\scheduler\Ticker\TickerStaticMethodCallbackTest', 'methodForTickCallback'],
            ],
        ],
        'scheduler_hop_callback' => [
            'class' => 'zaboy\scheduler\Callback\Instance',
            'params' => [
                'instanceServiceName' => 'test_scheduler',
                'instanceMethodName' => 'processHop',
            ],
        ],
        'scheduler_tick_callback' => [
            'class' => 'zaboy\scheduler\Callback\Instance',
            'params' => [
                'instanceServiceName' => 'test_scheduler',
                'instanceMethodName' => 'processTick',
            ],
        ],
        'script_example_tick_callback' => [
            'class' => 'zaboy\scheduler\Callback\Script',
            'params' => [
                'script_name' => 'src/Callback/Examples/tick.php',
            ],
        ],
        'test_scriptproxy_callback' => [
            'class' => 'zaboy\scheduler\Callback\ScriptProxy',
            'params' => [
                'rpc_callback' => 'script_example_tick_callback'
            ]
        ]
    ],


    'tasks' => [
        'task1' => [
            'id' => 1,
            'rql' => 'in(seconds,(3,8,10,15,20,23,33,41,55,59))',
            'callback' => 'tick_callback',
            'active' => 1
        ],
        'task2' => [
            'id' => 2,
            'rql' => 'in(seconds,(4,9,11,16,21,24,34,42,56))',
            'callback' => 'tick_callback',
            'active' => 1
        ],
    ]
];
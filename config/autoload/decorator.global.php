<?php

return [
    'services' => [
        'factories' => [
            'simple_class' => '\zaboy\test\scheduler\Callback\Factory\SimpleClassFactory'
        ],
        'abstract_factories' => [
//            'zaboy\scheduler\Callback\Decorators\Factory\ScriptDecoratorAbstractFactory',
        ],
    ],

    'callback' => [
        'test_async_decorator_with_script_callback' => [
            'class' => 'zaboy\scheduler\Callback\Decorators\ScriptDecorator',
            'rpc_callback' => 'script_tick_callback',
        ],
        'test_async_decorator_with_instance_callback' => [
            'class' => 'zaboy\scheduler\Callback\Decorators\ScriptDecorator',
            'rpc_callback' => 'test_instance_callback_via_decorator',
        ],
        'test_async_decorator_with_staticmethod_callback' => [
            'class' => 'zaboy\scheduler\Callback\Decorators\ScriptDecorator',
            'rpc_callback' => 'test_staticmethod_callback_via_decorator',
        ],


        'test_instance_callback_via_decorator' => [
            'class' => 'zaboy\scheduler\Callback\Instance',
            'params' => [
                'instanceServiceName' => 'simple_class',
                'instanceMethodName' => 'methodWhichLogsOneRow',
            ],
        ],
        'test_staticmethod_callback_via_decorator' => [
            'class' => 'zaboy\scheduler\Callback\StaticMethod',
            'method' => ['\zaboy\test\scheduler\Examples\Callback\SimpleClass', 'staticMethodWhichLogsOneRow'],
        ],
    ],
];


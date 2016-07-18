<?php

return [
    'callback' => [

        'test_scriptBroker_script_critical_error' => [
            'class' => 'zaboy\scheduler\Callback\Script',
            'script_name' => 'test/src/Broker/Examples/scriptCriticalError.php',
        ],
        'test_scriptBroker_script_exception' => [
            'class' => 'zaboy\scheduler\Callback\Script',
            'script_name' => 'test/src/Broker/Examples/scriptException.php',
        ],
        'test_scriptBroker_script_long_work' => [
            'class' => 'zaboy\scheduler\Callback\Script',
            'script_name' => 'test/src/Broker/Examples/scriptLongWork.php',
        ],
        'test_scriptBroker_script_normal' => [
            'class' => 'zaboy\scheduler\Callback\Script',
            'script_name' => 'test/src/Broker/Examples/scriptNormal.php',
        ],
        'test_scriptBroker_script_normal_with_warning' => [
            'class' => 'zaboy\scheduler\Callback\Script',
            'script_name' => 'test/src/Broker/Examples/scriptNormalWithWarning.php',
        ],
        'test_scriptBroker_script_syntax_error' => [
            'class' => 'zaboy\scheduler\Callback\Script',
            'script_name' => 'test/src/Broker/Examples/scriptSyntaxError.php',
        ],



        'test_scriptBroker_decorator_critical_error' => [
            'class' => 'zaboy\scheduler\Callback\Decorators\ScriptDecorator',
            'rpc_callback' => 'test_scriptBroker_script_critical_error',
        ],
        'test_scriptBroker_decorator_exception' => [
            'class' => 'zaboy\scheduler\Callback\Decorators\ScriptDecorator',
            'rpc_callback' => 'test_scriptBroker_script_exception',
        ],
        'test_scriptBroker_decorator_long_work' => [
            'class' => 'zaboy\scheduler\Callback\Decorators\ScriptDecorator',
            'rpc_callback' => 'test_scriptBroker_script_long_work',
        ],
        'test_scriptBroker_decorator_normal' => [
            'class' => 'zaboy\scheduler\Callback\Decorators\ScriptDecorator',
            'rpc_callback' => 'test_scriptBroker_script_normal',
        ],
        'test_scriptBroker_decorator_normal_with_warning' => [
            'class' => 'zaboy\scheduler\Callback\Decorators\ScriptDecorator',
            'rpc_callback' => 'test_scriptBroker_script_normal_with_warning',
        ],
        'test_scriptBroker_decorator_syntax_error' => [
            'class' => 'zaboy\scheduler\Callback\Decorators\ScriptDecorator',
            'rpc_callback' => 'test_scriptBroker_script_syntax_error',
        ],
    ]
];
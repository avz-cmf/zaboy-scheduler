<?php

namespace zaboy\test\Callback;

class ScriptCallbackTest extends CallbackAbstractTest
{
    protected function initCallback()
    {
        $callbackManager = $this->container->get('callback_manager');
        /** @var \zaboy\scheduler\Callback\Script $scriptCallback */
        $this->callback = $callbackManager->get('script_example_tick_callback');
    }

    public function test_call()
    {
        $options = [
            'param1' => 'value1',
            'param2' => ['value21', 'value22'],
        ];
        $this->callback->call($options);

        // Expected that in the log will be one entry
        $item = $this->log->read(1);
        $this->assertEquals(
            print_r($options, 1), $item['step']
        );
    }
}
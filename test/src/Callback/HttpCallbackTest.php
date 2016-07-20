<?php

namespace zaboy\test\Callback;

class HttpCallbackTest extends CallbackAbstractTest
{
    protected function initCallback()
    {
        $this->callback = $this->container->get('test_http_callback');
    }

    public function test_call()
    {
        $options = [
            'key1' => 'val1',
            'key2' => 'val2'
        ];
        $result = $this->callback->call($options);
        $this->assertEquals(
            $options,
            $result
        );
    }
}
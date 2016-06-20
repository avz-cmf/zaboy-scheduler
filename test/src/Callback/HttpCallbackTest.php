<?php

namespace zaboy\test\Callback;

class HttpCallbackTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Zend\ServiceManager\ServiceManager $container */
    protected $container;

    /** @var  \zaboy\scheduler\Callback\Interfaces\CallbackInterface */
    protected $callback;

    protected function setUp()
    {
        $this->container = include './config/container.php';
        $this->callback = $this->container->get('test_http_callback');
    }

    public function test_call()
    {
        $this->assertEquals(
            "I'm HttpCallback simple response!",
            $this->callback->call([])
        );
    }
}
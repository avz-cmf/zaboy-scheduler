<?php

namespace zaboy\scheduler\Callback;

use Interop\Container\ContainerInterface;
use zaboy\scheduler\Callback\Interfaces\CallbackInterface;
use Zend\ServiceManager\Exception;

class CallbackManager implements ContainerInterface
{
    const SERVICE_NAME = 'callback_manager';

    /** @var  \Zend\ServiceManager\ServiceManager $container */
    protected $container;

    /**
     * CallbackManager constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inherit}
     *
     * {@inherit}
     */
    public function get($name)
    {
        if (!$this->has($name)) {
            throw new CallbackException("The specified service with name \"{$name}\" does not exist");
        }
        $instance = $this->container->get($name);
        if (!$instance instanceof CallbackInterface) {
            throw new CallbackException("The instance of specified service is not Callback");
        }
        return $instance;
    }

    /**
     * {@inherit}
     *
     * {@inherit}
     */
    public function has($name)
    {
        $config = $this->container->get('config');
        return (isset($config['callback'][$name]));
    }


    public function __call($callbackServiceName, $arguments)
    {
        $arguments = array_shift($arguments);

        if (!$this->has($callbackServiceName)) {
            throw new CallbackException("The specified service name for callback \"{$callbackServiceName}\"
                was not found");
        }
        /** @var \zaboy\async\Promise\PromiseClient $promise */
        $promise = $arguments['promise'];
        unset($arguments['promise']);
        /** @var CallbackInterface $callback */
        $callback = $this->container->get($callbackServiceName);
        try {
            $result = $callback->call($arguments);
            $promise->resolve($result);
        } catch (\Exception $e) {
            $promise->reject($e);
        }
    }
}
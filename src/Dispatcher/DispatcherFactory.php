<?php

namespace SequentSoft\ThreadFlow\Dispatcher;

use InvalidArgumentException;
use Closure;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;

class DispatcherFactory implements DispatcherFactoryInterface
{
    protected array $registeredDispatchers = [];

    public function register(string $dispatcherName, Closure $callback): void
    {
        $this->registeredDispatchers[$dispatcherName] = $callback;
    }

    public function make(
        string $dispatcherName,
        string $channelName,
        EventBusInterface $eventBus,
        ConfigInterface $config,
        Closure $outgoing,
    ): DispatcherInterface {
        if (! isset($this->registeredDispatchers[$dispatcherName])) {
            throw new InvalidArgumentException("Dispatcher {$dispatcherName} is not registered.");
        }

        return call_user_func(
            $this->registeredDispatchers[$dispatcherName],
            $channelName,
            $eventBus,
            $config,
            $outgoing
        );
    }
}

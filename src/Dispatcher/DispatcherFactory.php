<?php

namespace SequentSoft\ThreadFlow\Dispatcher;

use InvalidArgumentException;
use Closure;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;

class DispatcherFactory implements DispatcherFactoryInterface
{
    protected array $registeredDispatchers = [];

    public function register(string $name, Closure $callback): void
    {
        $this->registeredDispatchers[$name] = $callback;
    }

    public function make(string $name, BotInterface $bot): DispatcherInterface
    {
        if (!isset($this->registeredDispatchers[$name])) {
            throw new InvalidArgumentException("Dispatcher {$name} is not registered.");
        }

        return call_user_func($this->registeredDispatchers[$name], $bot);
    }
}

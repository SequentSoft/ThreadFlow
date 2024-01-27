<?php

namespace SequentSoft\ThreadFlow\Contracts\Dispatcher;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;

interface DispatcherFactoryInterface
{
    public function register(string $dispatcherName, Closure $callback): void;

    public function make(
        string $dispatcherName,
        EventBusInterface $eventBus,
        ConfigInterface $config,
        Closure $outgoing,
    ): DispatcherInterface;
}

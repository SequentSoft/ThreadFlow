<?php

namespace SequentSoft\ThreadFlow\Contracts\Dispatcher;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;

interface DispatcherFactoryInterface
{
    public function registerDriver(string $dispatcherName, Closure $callback): void;

    public function make(
        string $dispatcherName,
        ?string $entryPage,
        EventBusInterface $eventBus,
        Closure $outgoing,
    ): DispatcherInterface;
}

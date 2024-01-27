<?php

namespace SequentSoft\ThreadFlow\Dispatcher;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;

class FakeDispatcherFactory extends DispatcherFactory
{
    public function make(
        string $dispatcherName,
        EventBusInterface $eventBus,
        ConfigInterface $config,
        Closure $outgoing,
    ): DispatcherInterface {
        return new FakeDispatcher(
            $this->pageFactory,
            $eventBus,
            $config,
            $outgoing
        );
    }
}

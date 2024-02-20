<?php

namespace SequentSoft\ThreadFlow\Dispatcher;

use Closure;
use SequentSoft\ThreadFlow\Config;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;

class FakeDispatcherFactory extends DispatcherFactory
{
    public function make(
        string $dispatcherName,
        ?string $entryPage,
        EventBusInterface $eventBus,
        Closure $outgoing,
    ): DispatcherInterface {
        return new FakeDispatcher(
            $eventBus,
            new Config([
                'driver' => 'fake',
                'entry' => $entryPage,
            ]),
            $outgoing
        );
    }
}

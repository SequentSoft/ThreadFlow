<?php

namespace SequentSoft\ThreadFlow\Dispatcher;

use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Page\ActivePagesRepositoryInterface;
use SequentSoft\ThreadFlow\Contracts\PendingMessages\PendingMessagesRepositoryInterface;

class FakeDispatcherFactory extends DispatcherFactory
{
    public function make(
        string $dispatcherName,
        EventBusInterface $eventBus,
        ActivePagesRepositoryInterface $activePagesRepository,
        PendingMessagesRepositoryInterface $pendingMessagesRepository,
    ): DispatcherInterface {
        return new FakeDispatcher(
            $eventBus,
            $activePagesRepository,
            $pendingMessagesRepository,
        );
    }
}

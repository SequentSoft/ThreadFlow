<?php

namespace SequentSoft\ThreadFlow\Contracts\Dispatcher;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Page\ActivePagesRepositoryInterface;
use SequentSoft\ThreadFlow\Contracts\PendingMessages\PendingMessagesRepositoryInterface;

interface DispatcherFactoryInterface
{
    public function registerDriver(string $dispatcherName, Closure $callback): void;

    public function make(
        string $dispatcherName,
        EventBusInterface $eventBus,
        ActivePagesRepositoryInterface $activePagesRepository,
        PendingMessagesRepositoryInterface $pendingMessagesRepository,
    ): DispatcherInterface;
}

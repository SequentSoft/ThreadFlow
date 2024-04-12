<?php

namespace SequentSoft\ThreadFlow\Dispatcher;

use Closure;
use InvalidArgumentException;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Page\ActivePagesRepositoryInterface;
use SequentSoft\ThreadFlow\Contracts\PendingMessages\PendingMessagesRepositoryInterface;

class DispatcherFactory implements DispatcherFactoryInterface
{
    protected array $drivers = [];

    public function __construct(
        protected ConfigInterface $config,
    ) {
    }

    public function registerDriver(string $dispatcherName, Closure $callback): void
    {
        $this->drivers[$dispatcherName] = $callback;
    }

    public function make(
        string $dispatcherName,
        EventBusInterface $eventBus,
        ActivePagesRepositoryInterface $activePagesRepository,
        PendingMessagesRepositoryInterface $pendingMessagesRepository,
    ): DispatcherInterface {
        $config = $this->config->get($dispatcherName);

        $driverName = $config['driver'] ?? null;

        if ($driverName === null) {
            throw new InvalidArgumentException("Dispatcher {$dispatcherName} is not configured.");
        }

        if (! isset($this->drivers[$driverName])) {
            throw new InvalidArgumentException("Dispatcher driver {$driverName} is not registered.");
        }

        return call_user_func(
            $this->drivers[$dispatcherName],
            $eventBus,
            $activePagesRepository,
            $pendingMessagesRepository
        );
    }
}

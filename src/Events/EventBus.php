<?php

namespace SequentSoft\ThreadFlow\Events;

use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;

class EventBus implements EventBusInterface
{
    protected array $listeners = [];

    /**
     * @param class-string $event
     * @param callable $callback
     * @return void
     */
    public function listen(string $event, callable $callback): void
    {
        $this->listeners[$event][] = $callback;
    }

    public function dispatch(object $event): void
    {
        $className = get_class($event);

        foreach ($this->listeners[$className] ?? [] as $listener) {
            $listener($event);
        }
    }
}

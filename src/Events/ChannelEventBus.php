<?php

namespace SequentSoft\ThreadFlow\Events;

use SequentSoft\ThreadFlow\Contracts\Events\ChannelEventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventInterface;

class ChannelEventBus implements ChannelEventBusInterface
{
    protected array $listeners = [];

    /**
     * @param string $event Event class name or '*' for all events
     * @param callable $callback
     * @return void
     */
    public function listen(string $event, callable $callback): void
    {
        $this->listeners[$event][] = $callback;
    }

    public function fire(EventInterface $event): void
    {
        $className = get_class($event);

        foreach ($this->listeners[$className] ?? [] as $listener) {
            $listener($event);
        }

        foreach ($this->listeners['*'] ?? [] as $listener) {
            $listener($event);
        }
    }
}

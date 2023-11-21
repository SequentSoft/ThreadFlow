<?php

namespace SequentSoft\ThreadFlow\Events;

use SequentSoft\ThreadFlow\Contracts\Events\ChannelEventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventInterface;

class EventBus implements EventBusInterface
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

    public function fire(string $channelName, EventInterface $event): void
    {
        $className = get_class($event);

        foreach ($this->listeners[$className] ?? [] as $listener) {
            $listener($channelName, $event);
        }

        foreach ($this->listeners['*'] ?? [] as $listener) {
            $listener($channelName, $event);
        }
    }

    public function makeChannelEventBus(string $channelName): ChannelEventBusInterface
    {
        $eventBus = new ChannelEventBus();

        $eventBus->listen('*', fn (EventInterface $event) => $this->fire($channelName, $event));

        return $eventBus;
    }
}
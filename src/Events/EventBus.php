<?php

namespace SequentSoft\ThreadFlow\Events;

use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventInterface;

class EventBus implements EventBusInterface
{
    public function __construct(
        protected ?string $name = null,
    ) {
    }

    protected array $listeners = [];

    public function listen(string $event, callable $callback): EventBusInterface
    {
        $this->listeners[$event][] = $callback;

        return $this;
    }

    public function fire(EventInterface $event): void
    {
        $this->fireWithName($this->name, $event);
    }

    private function fireWithName(?string $name, EventInterface $event): void
    {
        $className = get_class($event);

        foreach ($this->listeners[$className] ?? [] as $listener) {
            $listener($name, $event);
        }

        foreach ($this->listeners['*'] ?? [] as $listener) {
            $listener($name, $event);
        }
    }

    public function nested(string $name): EventBusInterface
    {
        $eventBus = new static($name);

        $eventBus->listen(
            '*',
            fn(?string $name, EventInterface $event) => $this->fireWithName($name ?? $this->name, $event)
        );

        return $eventBus;
    }
}

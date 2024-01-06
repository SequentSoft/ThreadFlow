<?php

namespace SequentSoft\ThreadFlow\Contracts\Events;

interface EventBusInterface
{
    public function listen(string $event, callable $callback): EventBusInterface;

    public function fire(EventInterface $event): void;

    public function nested(string $name): EventBusInterface;

    public function getListeners(): array;

    public function setListeners(array $listeners): void;
}

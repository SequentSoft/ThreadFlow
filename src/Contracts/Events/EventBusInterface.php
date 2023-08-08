<?php

namespace SequentSoft\ThreadFlow\Contracts\Events;

interface EventBusInterface
{
    public function listen(string $event, callable $callback): void;

    public function dispatch(object $event): void;
}

<?php

namespace SequentSoft\ThreadFlow\Contracts\Events;

interface ChannelEventBusInterface
{
    /**
     * @param class-string<EventInterface> $event
     * @param callable $callback
     * @return void
     */
    public function listen(string $event, callable $callback): void;

    public function fire(EventInterface $event): void;
}

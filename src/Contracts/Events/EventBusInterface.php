<?php

namespace SequentSoft\ThreadFlow\Contracts\Events;

interface EventBusInterface
{
    /**
     * @param class-string<EventInterface> $event
     * @param callable $callback
     * @return void
     */
    public function listen(string $event, callable $callback): void;

    public function fire(string $channelName, EventInterface $event): void;

    public function makeChannelEventBus(string $channelName): ChannelEventBusInterface;
}

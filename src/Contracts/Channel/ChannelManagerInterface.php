<?php

namespace SequentSoft\ThreadFlow\Contracts\Channel;

use Closure;

interface ChannelManagerInterface
{
    public function registerChannelDriver(string $channelName, Closure $callback): void;

    public function registerExceptionHandler(Closure $callback): void;

    public function handleProcessingExceptions(Closure $callback): void;

    public function on(string $event, callable $callback): void;

    public function channel(string $channelName): ChannelInterface;
}

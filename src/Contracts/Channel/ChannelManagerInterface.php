<?php

namespace SequentSoft\ThreadFlow\Contracts\Channel;

use Closure;

interface ChannelManagerInterface
{
    public function setUserResolver(?Closure $userResolver): void;

    public function getUserResolver(): ?Closure;

    public function registerChannelDriver(string $driverName, Closure $callback): void;

    public function registerExceptionHandler(Closure $callback): void;

    public function getExceptionsHandlers(): array;

    public function disableExceptionsHandlers(): void;

    public function on(string $event, callable $callback): void;

    public function channel(string $channelName): ChannelInterface;

    public function getRegisteredChannelDrivers(): array;
}

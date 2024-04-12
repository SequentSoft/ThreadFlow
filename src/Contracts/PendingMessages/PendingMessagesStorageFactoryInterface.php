<?php

namespace SequentSoft\ThreadFlow\Contracts\PendingMessages;

use Closure;

interface PendingMessagesStorageFactoryInterface
{
    public function registerDriver(string $name, Closure $callback): void;

    public function make(string $name): PendingMessagesStorageInterface;
}

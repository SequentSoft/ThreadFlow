<?php

namespace SequentSoft\ThreadFlow\Contracts\Dispatcher;

use Closure;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;

interface DispatcherInterface
{
    public function dispatch(
        string $channelName,
        IncomingMessageInterface $message,
        Closure $process
    ): void;
}

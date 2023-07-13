<?php

namespace SequentSoft\ThreadFlow\Contracts\Dispatcher;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;

interface DispatcherInterface
{
    public function dispatch(
        string $channelName,
        IncomingMessageInterface $message,
        ?Closure $incomingCallback = null,
        ?Closure $outgoingCallback = null
    ): void;
}

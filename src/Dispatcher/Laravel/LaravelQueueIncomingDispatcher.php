<?php

namespace SequentSoft\ThreadFlow\Dispatcher\Laravel;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;

class LaravelQueueIncomingDispatcher implements DispatcherInterface
{
    public function dispatch(
        string $channelName,
        IncomingMessageInterface $message,
        ?Closure $incomingCallback = null,
        ?Closure $outgoingCallback = null
    ): void {
        IncomingMessageJob::dispatch($channelName, $message);
    }
}

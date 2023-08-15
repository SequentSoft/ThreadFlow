<?php

namespace SequentSoft\ThreadFlow\Dispatcher\Laravel;

use Closure;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;

class LaravelQueueIncomingDispatcher implements DispatcherInterface
{
    public function dispatch(
        BotInterface $bot,
        IncomingMessageInterface $message,
        ?Closure $incomingCallback = null
    ): void {
        IncomingMessageJob::dispatch($bot->getChannelName(), $message);
    }
}

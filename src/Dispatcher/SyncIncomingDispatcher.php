<?php

namespace SequentSoft\ThreadFlow\Dispatcher;

use Closure;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;

class SyncIncomingDispatcher implements DispatcherInterface
{
    public function dispatch(
        BotInterface $bot,
        IncomingMessageInterface $message,
        ?Closure $incomingCallback = null
    ): void {
        $bot->process($message, $incomingCallback);
    }
}

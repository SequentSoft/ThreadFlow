<?php

namespace SequentSoft\ThreadFlow\Dispatcher;

use Closure;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;

class SyncIncomingDispatcher implements DispatcherInterface
{
    public function dispatch(
        string $channelName,
        IncomingMessageInterface $message,
        Closure $process
    ): void {
        $process($message);
    }
}

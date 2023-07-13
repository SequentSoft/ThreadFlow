<?php

namespace SequentSoft\ThreadFlow\Dispatcher;

use Closure;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;

class SyncIncomingDispatcher implements DispatcherInterface
{
    public function __construct(
        protected BotInterface $bot
    ) {
    }

    public function dispatch(
        string $channelName,
        IncomingMessageInterface $message,
        ?Closure $incomingCallback = null,
        ?Closure $outgoingCallback = null
    ): void {
        $this->bot->process(
            $channelName,
            $message,
            $incomingCallback,
            $outgoingCallback
        );
    }
}

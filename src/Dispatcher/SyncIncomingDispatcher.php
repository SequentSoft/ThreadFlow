<?php

namespace SequentSoft\ThreadFlow\Dispatcher;

use Closure;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Exceptions\Channel\ChannelNotConfiguredException;

class SyncIncomingDispatcher implements DispatcherInterface
{
    public function __construct(
        protected BotInterface $bot
    ) {
    }

    /**
     * @throws ChannelNotConfiguredException
     */
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

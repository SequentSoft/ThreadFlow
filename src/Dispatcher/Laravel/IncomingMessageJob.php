<?php

namespace SequentSoft\ThreadFlow\Dispatcher\Laravel;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Incoming\IncomingChannelRegistryInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Outgoing\OutgoingChannelRegistryInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class IncomingMessageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected string $channelName,
        protected IncomingMessageInterface $message
    ) {
    }

    public function handle(
        BotInterface $bot,
        IncomingChannelRegistryInterface $incomingChannelRegistry,
        OutgoingChannelRegistryInterface $outgoingChannelRegistry,
    ): void {
        $channelConfig = $bot->getChannelConfig($this->channelName);
        $incomingChannel = $incomingChannelRegistry->get($this->channelName, $channelConfig);
        $outgoingChannel = $outgoingChannelRegistry->get($this->channelName, $channelConfig);

        $bot->process(
            $this->channelName,
            $this->message,
            fn(
                IncomingMessageInterface $message,
                SessionInterface $session
            ) => $incomingChannel->preprocess($message, $session),
            fn(
                OutgoingMessageInterface $message,
                SessionInterface $session
            ) => $outgoingChannel->send($message, $session),
        );
    }
}

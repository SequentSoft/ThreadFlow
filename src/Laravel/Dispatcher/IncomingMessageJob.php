<?php

namespace SequentSoft\ThreadFlow\Laravel\Dispatcher;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SequentSoft\ThreadFlow\Contracts\Channel\ChannelManagerInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\BasicIncomingMessageInterface;

class IncomingMessageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected BasicIncomingMessageInterface $message
    ) {
    }

    public function handle(ChannelManagerInterface $channelManager): void
    {
        LaravelQueueIncomingDispatcher::sync(function () use ($channelManager) {
            $channel = $channelManager->channel($this->message->getContext()->getChannelName());
            $channel->incoming($this->message);
        });
    }
}

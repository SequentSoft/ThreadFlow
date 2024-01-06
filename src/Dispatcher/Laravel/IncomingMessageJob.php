<?php

namespace SequentSoft\ThreadFlow\Dispatcher\Laravel;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SequentSoft\ThreadFlow\Contracts\Channel\ChannelManagerInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;

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

    public function handle(ChannelManagerInterface $channelManager): void
    {
        LaravelQueueIncomingDispatcher::sync(function () use ($channelManager) {
            $channel = $channelManager->channel($this->channelName);
            $channel->incoming($this->message);
        });
    }
}

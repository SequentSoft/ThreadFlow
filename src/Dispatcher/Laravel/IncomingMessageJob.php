<?php

namespace SequentSoft\ThreadFlow\Dispatcher\Laravel;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SequentSoft\ThreadFlow\Contracts\BotManagerInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Dispatcher\SyncIncomingDispatcher;
use SequentSoft\ThreadFlow\Exceptions\Config\InvalidNestedConfigException;

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

    public function handle(BotManagerInterface $botManager): void
    {
        $bot = $botManager->channel($this->channelName);

        $bot->setDispatcher(new SyncIncomingDispatcher());

        $bot->dispatch($this->message);
    }
}

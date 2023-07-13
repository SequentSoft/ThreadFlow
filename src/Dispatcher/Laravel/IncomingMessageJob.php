<?php

namespace SequentSoft\ThreadFlow\Dispatcher\Laravel;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
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

    public function handle(BotInterface $bot)
    {
        $incomingChannel = $bot->getIncomingChannel($this->channelName);
        $outgoingChannel = $bot->getOutgoingChannel($this->channelName);

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

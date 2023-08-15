<?php

namespace SequentSoft\ThreadFlow\Contracts\Dispatcher;

use Closure;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;

interface DispatcherInterface
{
    public function dispatch(
        BotInterface $bot,
        IncomingMessageInterface $message,
        ?Closure $incomingCallback = null
    ): void;
}

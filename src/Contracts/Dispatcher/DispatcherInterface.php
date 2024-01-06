<?php

namespace SequentSoft\ThreadFlow\Contracts\Dispatcher;

use Closure;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

interface DispatcherInterface
{
    public function incoming(
        IncomingMessageInterface $message,
        SessionInterface $session
    ): void;
}

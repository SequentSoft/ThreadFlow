<?php

namespace SequentSoft\ThreadFlow\Contracts\Dispatcher;

use Closure;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PendingDispatchPageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

interface DispatcherInterface
{
    public function incoming(
        IncomingMessageInterface $message,
        SessionInterface $session
    ): void;

    public function outgoing(
        OutgoingMessageInterface $message,
        ?SessionInterface $session,
        ?PageInterface $page
    ): OutgoingMessageInterface;

    public function transition(
        MessageContextInterface $messageContext,
        SessionInterface $session,
        PendingDispatchPageInterface $pendingDispatchPage,
        ?PageInterface $contextPage = null
    ): void;
}

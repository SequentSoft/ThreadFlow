<?php

namespace SequentSoft\ThreadFlow\Contracts\Dispatcher;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\CommonIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\CommonOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

interface DispatcherInterface
{
    public function incoming(
        CommonIncomingMessageInterface $message,
        SessionInterface               $session
    ): void;

    public function outgoing(
        CommonOutgoingMessageInterface $message,
        ?SessionInterface              $session,
        ?PageInterface                 $page
    ): CommonOutgoingMessageInterface;

    public function transition(
        MessageContextInterface $messageContext,
        SessionInterface $session,
        PageInterface $page,
        ?PageInterface $contextPage = null
    ): void;
}

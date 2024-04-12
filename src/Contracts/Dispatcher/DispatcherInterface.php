<?php

namespace SequentSoft\ThreadFlow\Contracts\Dispatcher;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\BasicIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\BasicOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

interface DispatcherInterface
{
    public function setOutgoingCallback(Closure $outgoingCallback): void;

    public function pushPendingMessage(
        MessageContextInterface $messageContext,
        SessionInterface $session,
        PageInterface|BasicOutgoingMessageInterface $pageOrMessage
    ): void;

    public function incoming(
        BasicIncomingMessageInterface $message,
        SessionInterface $session,
        PageInterface $page,
    ): void;

    public function outgoing(
        BasicOutgoingMessageInterface $message,
        ?SessionInterface $session,
        ?PageInterface $page
    ): BasicOutgoingMessageInterface;

    public function transition(
        MessageContextInterface $messageContext,
        SessionInterface $session,
        PageInterface $page,
        ?PageInterface $contextPage = null
    ): void;
}

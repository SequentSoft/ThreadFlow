<?php

namespace SequentSoft\ThreadFlow\Events\Message;

use SequentSoft\ThreadFlow\Contracts\Events\EventInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\CommonOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class OutgoingMessageSendingEvent implements EventInterface
{
    public function __construct(
        protected CommonOutgoingMessageInterface $message,
        protected SessionInterface               $session,
        protected ?PageInterface                 $contextPage = null,
    ) {
    }

    public function getMessage(): CommonOutgoingMessageInterface
    {
        return $this->message;
    }

    public function getSession(): SessionInterface
    {
        return $this->session;
    }
}

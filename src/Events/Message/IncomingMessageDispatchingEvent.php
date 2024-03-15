<?php

namespace SequentSoft\ThreadFlow\Events\Message;

use SequentSoft\ThreadFlow\Contracts\Events\EventInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\CommonIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class IncomingMessageDispatchingEvent implements EventInterface
{
    public function __construct(
        protected CommonIncomingMessageInterface $message,
        protected SessionInterface $session,
    ) {
    }

    public function getMessage(): CommonIncomingMessageInterface
    {
        return $this->message;
    }

    public function getSession(): SessionInterface
    {
        return $this->session;
    }
}

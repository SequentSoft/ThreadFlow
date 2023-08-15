<?php

namespace SequentSoft\ThreadFlow\Events\Message;

use SequentSoft\ThreadFlow\Contracts\Events\EventInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class IncomingMessageDispatchingEvent implements EventInterface
{
    public function __construct(
        protected IncomingMessageInterface $message
    ) {
    }

    public function getMessage(): IncomingMessageInterface
    {
        return $this->message;
    }
}

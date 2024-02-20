<?php

namespace SequentSoft\ThreadFlow\Events\Message;

use SequentSoft\ThreadFlow\Contracts\Events\EventInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\CommonIncomingMessageInterface;

class IncomingMessageDispatchingEvent implements EventInterface
{
    public function __construct(
        protected CommonIncomingMessageInterface $message
    ) {
    }

    public function getMessage(): CommonIncomingMessageInterface
    {
        return $this->message;
    }
}

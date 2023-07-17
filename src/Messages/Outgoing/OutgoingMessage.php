<?php

namespace SequentSoft\ThreadFlow\Messages\Outgoing;

use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Messages\Message;

abstract class OutgoingMessage extends Message implements OutgoingMessageInterface
{
    public function ignore(): IgnoreOutgoingMessage
    {
        return new IgnoreOutgoingMessage();
    }
}

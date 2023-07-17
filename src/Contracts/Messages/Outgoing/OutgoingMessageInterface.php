<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Outgoing;

use SequentSoft\ThreadFlow\Contracts\Messages\MessageInterface;
use SequentSoft\ThreadFlow\Messages\Outgoing\IgnoreOutgoingMessage;

interface OutgoingMessageInterface extends MessageInterface
{
    public function ignore(): IgnoreOutgoingMessage;
}

<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular;

use SequentSoft\ThreadFlow\Contracts\Messages\MessageInterface;

interface ForwardOutgoingMessageInterface extends OutgoingMessageInterface
{
    public function getTargetMessage(): MessageInterface;
}

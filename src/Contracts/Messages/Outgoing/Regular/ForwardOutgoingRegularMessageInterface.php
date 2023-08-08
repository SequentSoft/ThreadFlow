<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular;

use SequentSoft\ThreadFlow\Contracts\Messages\MessageInterface;

interface ForwardOutgoingRegularMessageInterface extends OutgoingRegularMessageInterface
{
    public function getTargetMessage(): MessageInterface;
}

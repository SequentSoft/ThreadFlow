<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular;

use SequentSoft\ThreadFlow\Contracts\Keyboard\KeyboardInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface;

interface OutgoingRegularMessageInterface extends OutgoingMessageInterface
{
    public function getKeyboard(): ?KeyboardInterface;
}

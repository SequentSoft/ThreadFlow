<?php

namespace SequentSoft\ThreadFlow\Messages\Outgoing\Regular;

use SequentSoft\ThreadFlow\Contracts\Keyboard\KeyboardInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\OutgoingRegularMessageInterface;
use SequentSoft\ThreadFlow\Keyboard\Keyboard;
use SequentSoft\ThreadFlow\Messages\Outgoing\OutgoingMessage;

abstract class OutgoingRegularMessage extends OutgoingMessage implements OutgoingRegularMessageInterface
{
    protected ?KeyboardInterface $keyboard = null;

    public function withKeyboard(
        KeyboardInterface|array|null $keyboard
    ): OutgoingRegularMessageInterface {
        $this->keyboard = is_array($keyboard)
            ? Keyboard::createFromArray($keyboard)
            : $keyboard;
        return $this;
    }

    public function getKeyboard(): ?KeyboardInterface
    {
        return $this->keyboard;
    }
}

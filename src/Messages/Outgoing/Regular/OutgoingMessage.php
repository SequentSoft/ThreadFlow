<?php

namespace SequentSoft\ThreadFlow\Messages\Outgoing\Regular;

use SequentSoft\ThreadFlow\Contracts\Keyboard\BaseKeyboardInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\SimpleKeyboardInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Keyboard\Keyboard;
use SequentSoft\ThreadFlow\Messages\Outgoing\BasicOutgoingMessage;

abstract class OutgoingMessage extends BasicOutgoingMessage implements OutgoingMessageInterface
{
    protected ?BaseKeyboardInterface $keyboard = null;

    public function withKeyboard(
        BaseKeyboardInterface|array|null $keyboard,
        ?string $placeholder = null,
    ): OutgoingMessageInterface {
        if (is_null($keyboard)) {
            $this->keyboard = null;

            return $this;
        }

        $this->keyboard = is_array($keyboard)
            ? Keyboard::createFromArray($keyboard)
            : $keyboard;

        if ($this->keyboard instanceof SimpleKeyboardInterface && $placeholder) {
            $this->keyboard->placeholder($placeholder);
        }

        return $this;
    }

    public function getKeyboard(): ?BaseKeyboardInterface
    {
        return $this->keyboard;
    }
}

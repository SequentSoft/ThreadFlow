<?php

namespace SequentSoft\ThreadFlow\Messages\Outgoing\Regular;

use SequentSoft\ThreadFlow\Contracts\Keyboard\KeyboardInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Keyboard\Keyboard;
use SequentSoft\ThreadFlow\Messages\Outgoing\CommonOutgoingMessage;

abstract class OutgoingMessage extends CommonOutgoingMessage implements OutgoingMessageInterface
{
    protected ?KeyboardInterface $keyboard = null;

    public function withKeyboard(
        KeyboardInterface|array|null $keyboard,
        ?string $placeholder = null,
    ): OutgoingMessageInterface {
        if (is_null($keyboard)) {
            $this->keyboard = null;

            return $this;
        }

        $this->keyboard = is_array($keyboard)
            ? Keyboard::createFromArray($keyboard)
            : $keyboard;

        if ($placeholder) {
            $this->keyboard->placeholder($placeholder);
        }

        return $this;
    }

    public function getKeyboard(): ?KeyboardInterface
    {
        return $this->keyboard;
    }
}

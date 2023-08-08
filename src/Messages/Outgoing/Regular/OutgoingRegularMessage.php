<?php

namespace SequentSoft\ThreadFlow\Messages\Outgoing\Regular;

use SequentSoft\ThreadFlow\Contracts\Keyboard\KeyboardInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\OutgoingRegularMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Keyboard\Keyboard;
use SequentSoft\ThreadFlow\Messages\Outgoing\OutgoingMessage;

abstract class OutgoingRegularMessage extends OutgoingMessage implements OutgoingRegularMessageInterface
{
    protected ?KeyboardInterface $keyboard = null;

    public function withKeyboard(
        KeyboardInterface|array|null $keyboard,
        ?string $placeholder = null,
    ): OutgoingRegularMessageInterface {
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

<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Outgoing;

use SequentSoft\ThreadFlow\Contracts\Keyboard\KeyboardInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\OutgoingMessageInterface;

interface WithKeyboardInterface
{
    public function getKeyboard(): ?KeyboardInterface;

    public function withKeyboard(
        KeyboardInterface|array|null $keyboard,
        ?string $placeholder = null,
    ): OutgoingMessageInterface;
}

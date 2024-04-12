<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Outgoing;

use SequentSoft\ThreadFlow\Contracts\Keyboard\BaseKeyboardInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\OutgoingMessageInterface;

interface WithKeyboardInterface
{
    public function getKeyboard(): ?BaseKeyboardInterface;

    public function withKeyboard(
        BaseKeyboardInterface|array|null $keyboard,
        ?string $placeholder = null,
    ): OutgoingMessageInterface;
}

<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Outgoing;

use SequentSoft\ThreadFlow\Contracts\Keyboard\KeyboardInterface;

interface WithKeyboardInterface
{
    public function getKeyboard(): ?KeyboardInterface;
}

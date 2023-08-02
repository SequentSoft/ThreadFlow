<?php

namespace SequentSoft\ThreadFlow\Contracts\Keyboard;

interface CommonKeyboardInterface extends KeyboardInterface
{
    public function oneTimeKeyboard(bool $oneTime = true): self;
}

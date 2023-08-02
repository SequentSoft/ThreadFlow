<?php

namespace SequentSoft\ThreadFlow\Keyboard;

use SequentSoft\ThreadFlow\Contracts\Keyboard\CommonKeyboardInterface;

class CommonKeyboard extends Keyboard implements CommonKeyboardInterface
{
    protected bool $oneTime = false;

    public function oneTimeKeyboard(bool $oneTime = true): CommonKeyboardInterface
    {
        $this->oneTime = $oneTime;

        return $this;
    }

    public function isOneTime(): bool
    {
        return $this->oneTime;
    }
}

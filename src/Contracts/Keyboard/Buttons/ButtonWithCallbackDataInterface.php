<?php

namespace SequentSoft\ThreadFlow\Contracts\Keyboard\Buttons;

use SequentSoft\ThreadFlow\Contracts\Keyboard\ButtonInterface;

interface ButtonWithCallbackDataInterface extends ButtonInterface
{
    public function getCallbackData(): ?string;

    public function setCallbackData(?string $callbackData): ButtonWithCallbackDataInterface;
}

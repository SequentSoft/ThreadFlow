<?php

namespace SequentSoft\ThreadFlow\Contracts\Keyboard\Buttons;

use SequentSoft\ThreadFlow\Contracts\Keyboard\ButtonInterface;

interface TextButtonInterface extends ButtonInterface
{
    public function getCallbackData(): ?string;

    public function answerAsText(): TextButtonInterface;

    public function setCallbackData(?string $callbackData): TextButtonInterface;
}

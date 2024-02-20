<?php

namespace SequentSoft\ThreadFlow\Contracts\Keyboard\Buttons;

use SequentSoft\ThreadFlow\Contracts\Keyboard\ButtonInterface;

interface BackButtonInterface extends ButtonInterface
{
    public function getCallbackData(): ?string;

    public function setCallbackData(?string $callbackData): BackButtonInterface;

    public function answerAsText(): BackButtonInterface;

    public function autoHandleAnswer(): BackButtonInterface;

    public function isAutoHandleAnswer(): bool;
}

<?php

namespace SequentSoft\ThreadFlow\Contracts\Keyboard\Buttons;

interface BackButtonInterface extends ButtonWithCallbackDataInterface
{
    public function answerAsText(): BackButtonInterface;

    public function isAutoHandleAnswer(): bool;
}

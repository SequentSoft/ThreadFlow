<?php

namespace SequentSoft\ThreadFlow\Contracts\Keyboard\Buttons;

use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;

interface TextButtonInterface extends ButtonWithCallbackDataInterface
{
    public function answerAsText(): TextButtonInterface;

    public function getAutoHandleAnswerPage(): ?PageInterface;
}

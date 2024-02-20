<?php

namespace SequentSoft\ThreadFlow\Contracts\Keyboard\Buttons;

use SequentSoft\ThreadFlow\Contracts\Keyboard\ButtonInterface;

interface ContactButtonInterface extends ButtonInterface
{
    public function answerAsText(): ContactButtonInterface;
}

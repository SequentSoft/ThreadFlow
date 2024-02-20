<?php

namespace SequentSoft\ThreadFlow\Contracts\Keyboard\Buttons;

use SequentSoft\ThreadFlow\Contracts\Keyboard\ButtonInterface;

interface LocationButtonInterface extends ButtonInterface
{
    public function answerAsText(): LocationButtonInterface;
}

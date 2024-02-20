<?php

namespace SequentSoft\ThreadFlow\Contracts\Keyboard;

interface ButtonInterface
{
    public function getTitle(): string;

    public function isAnswerAsText(): bool;
}

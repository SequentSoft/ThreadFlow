<?php

namespace SequentSoft\ThreadFlow\Contracts\Keyboard;

interface ButtonInterface
{
    public function getText(): string;
    public function getCallbackData(): ?string;
}

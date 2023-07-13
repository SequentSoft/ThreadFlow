<?php

namespace SequentSoft\ThreadFlow\Keyboard;

use SequentSoft\ThreadFlow\Contracts\Keyboard\ButtonInterface;

class Button implements ButtonInterface
{
    public function __construct(
        protected string $text,
        protected ?string $callbackData = null,
    ) {
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getCallbackData(): ?string
    {
        return $this->callbackData;
    }
}

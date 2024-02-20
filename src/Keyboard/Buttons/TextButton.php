<?php

namespace SequentSoft\ThreadFlow\Keyboard\Buttons;

use SequentSoft\ThreadFlow\Contracts\Keyboard\Buttons\TextButtonInterface;

class TextButton implements TextButtonInterface
{
    protected bool $answerAsText = false;

    public function __construct(
        protected string $title,
        protected ?string $callbackData = null,
    ) {
    }

    public function answerAsText(): TextButtonInterface
    {
        $this->answerAsText = true;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function isAnswerAsText(): bool
    {
        return $this->answerAsText;
    }

    public function getCallbackData(): ?string
    {
        return $this->callbackData;
    }

    public function setCallbackData(?string $callbackData): TextButtonInterface
    {
        $this->callbackData = $callbackData;

        return $this;
    }
}

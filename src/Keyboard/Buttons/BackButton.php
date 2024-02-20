<?php

namespace SequentSoft\ThreadFlow\Keyboard\Buttons;

use SequentSoft\ThreadFlow\Contracts\Keyboard\Buttons\BackButtonInterface;

class BackButton implements BackButtonInterface
{
    protected bool $answerAsText = false;

    protected bool $autoHandleAnswer = false;

    public function __construct(
        protected string $title,
        protected ?string $callbackData = null,
    ) {
    }

    public function answerAsText(): BackButtonInterface
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

    public function autoHandleAnswer(): BackButtonInterface
    {
        $this->autoHandleAnswer = true;

        return $this;
    }

    public function isAutoHandleAnswer(): bool
    {
        return $this->autoHandleAnswer;
    }

    public function setCallbackData(?string $callbackData): BackButtonInterface
    {
        $this->callbackData = $callbackData;

        return $this;
    }
}

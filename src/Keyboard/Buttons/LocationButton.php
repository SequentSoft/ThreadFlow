<?php

namespace SequentSoft\ThreadFlow\Keyboard\Buttons;

use SequentSoft\ThreadFlow\Contracts\Keyboard\Buttons\LocationButtonInterface;

class LocationButton implements LocationButtonInterface
{
    protected bool $answerAsText = false;

    public function __construct(
        protected string $title,
    ) {
    }

    public function answerAsText(): LocationButtonInterface
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
}

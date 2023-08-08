<?php

namespace SequentSoft\ThreadFlow\Keyboard;

use SequentSoft\ThreadFlow\Contracts\Keyboard\CommonKeyboardInterface;

class Keyboard extends BaseKeyboard implements CommonKeyboardInterface
{
    protected bool $oneTime = false;

    protected bool $resizable = false;

    protected string $placeholder = '';

    public function oneTimeKeyboard(bool $oneTime = true): CommonKeyboardInterface
    {
        $this->oneTime = $oneTime;

        return $this;
    }

    public function isOneTime(): bool
    {
        return $this->oneTime;
    }

    public function resizable(bool $resizable = true): self
    {
        $this->resizable = $resizable;

        return $this;
    }

    public function isResizable(): bool
    {
        return $this->resizable;
    }

    public function placeholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function getPlaceholder(): string
    {
        return $this->placeholder;
    }
}

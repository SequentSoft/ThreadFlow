<?php

namespace SequentSoft\ThreadFlow\Keyboard;

use SequentSoft\ThreadFlow\Contracts\Keyboard\SimpleKeyboardInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\InlineKeyboardInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\BaseKeyboardInterface;

class Keyboard extends BaseKeyboard implements SimpleKeyboardInterface
{
    protected bool $oneTime = false;

    protected bool $resizable = true;

    protected string $placeholder = '';

    public static function make(): static
    {
        return new static([]);
    }

    public static function makeFromKeyboard(BaseKeyboardInterface $keyboard): SimpleKeyboardInterface
    {
        return new static($keyboard->getRows());
    }

    public function oneTimeKeyboard(bool $oneTime = true): SimpleKeyboardInterface
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

    public function notResizable(): self
    {
        return $this->resizable(false);
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

    public function inline(): InlineKeyboardInterface
    {
        return InlineKeyboard::makeFromKeyboard($this);
    }
}

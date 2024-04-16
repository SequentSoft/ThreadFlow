<?php

namespace SequentSoft\ThreadFlow\Contracts\Keyboard;

interface SimpleKeyboardInterface extends BaseKeyboardInterface
{
    public function oneTimeKeyboard(bool $oneTime = true): self;

    public function resizable(bool $resizable = true): self;

    public function notResizable(): self;

    public function placeholder(string $placeholder): self;

    public function isOneTime(): bool;

    public function isResizable(): bool;

    public function getPlaceholder(): string;

    public function inline(): InlineKeyboardInterface;
}

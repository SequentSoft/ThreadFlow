<?php

namespace SequentSoft\ThreadFlow\Contracts\Keyboard;

interface ButtonInterface
{
    public function getText(): string;
    public function getCallbackData(): ?string;

    public function callbackData(?string $callbackData): static;

    public function isRequestContact(): bool;
    public function isRequestLocation(): bool;
}

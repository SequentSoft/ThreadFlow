<?php

namespace SequentSoft\ThreadFlow\Contracts\Keyboard;

interface ButtonInterface
{
    public function getText(): string;
    public function getCallbackData(): ?string;
    public function isRequestContact(): bool;
    public function isRequestLocation(): bool;
}

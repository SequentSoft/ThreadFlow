<?php

namespace SequentSoft\ThreadFlow\Keyboard;

use SequentSoft\ThreadFlow\Contracts\Keyboard\ButtonInterface;

class Button implements ButtonInterface
{
    protected bool $requestContact = false;

    protected bool $requestLocation = false;

    final public function __construct(
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

    public function isRequestContact(): bool
    {
        return $this->requestContact;
    }

    public function isRequestLocation(): bool
    {
        return $this->requestLocation;
    }

    public function setCallbackData(?string $callbackData): static
    {
        $this->callbackData = $callbackData;
        return $this;
    }

    public function setRequestContact(bool $requestContact): static
    {
        $this->requestContact = $requestContact;
        return $this;
    }

    public function setRequestLocation(bool $requestLocation): static
    {
        $this->requestLocation = $requestLocation;
        return $this;
    }

    protected static function make(string $text, ?string $callbackData = null): static
    {
        return new static($text, $callbackData);
    }

    public static function text(string $text, ?string $callbackData = null): static
    {
        return static::make($text, $callbackData);
    }

    public static function contact(string $text, ?string $callbackData = null): static
    {
        $button = static::make($text, $callbackData);
        $button->setRequestContact(true);
        return $button;
    }

    public static function location(string $text, ?string $callbackData = null): static
    {
        $button = static::make($text, $callbackData);
        $button->setRequestLocation(true);
        return $button;
    }
}

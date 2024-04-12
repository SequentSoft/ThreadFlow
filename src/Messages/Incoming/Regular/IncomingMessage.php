<?php

namespace SequentSoft\ThreadFlow\Messages\Incoming\Regular;

use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\AudioIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\ClickIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\ContactIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\FileIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\ImageIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\LocationIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\StickerIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\TextIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\VideoIncomingMessageInterface;
use SequentSoft\ThreadFlow\Messages\Incoming\BasicIncomingMessage;

class IncomingMessage extends BasicIncomingMessage implements IncomingMessageInterface
{
    protected string $text = '[message]';

    public function isClicked(string $key): bool
    {
        if (! $this instanceof ClickIncomingMessageInterface) {
            return false;
        }

        return $this->getKey() === $key;
    }

    public function isText(?string $text = null): bool
    {
        if (! $this instanceof TextIncomingMessageInterface) {
            return false;
        }

        return is_null($text) || $this->getText() === $text;
    }

    public function isTextAndContains(string $text): bool
    {
        if (! $this->isText()) {
            return false;
        }

        return str_contains($this->getText(), $text);
    }

    public function isTextAndMatch(string $expression): bool
    {
        if (! $this->isText()) {
            return false;
        }

        return preg_match($expression, $this->getText()) > 0;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function isLocation(): bool
    {
        return $this instanceof LocationIncomingMessageInterface;
    }

    public function isContact(): bool
    {
        return $this instanceof ContactIncomingMessageInterface;
    }

    public function isFile(): bool
    {
        return $this instanceof FileIncomingMessageInterface;
    }

    public function isSticker(): bool
    {
        return $this instanceof StickerIncomingMessageInterface;
    }

    public function isVideo(): bool
    {
        return $this instanceof VideoIncomingMessageInterface;
    }

    public function isImage(): bool
    {
        return $this instanceof ImageIncomingMessageInterface;
    }

    public function isAudio(): bool
    {
        return $this instanceof AudioIncomingMessageInterface;
    }

    public function isFormResult(): bool
    {
        return $this instanceof FormResultIncomingMessage;
    }
}

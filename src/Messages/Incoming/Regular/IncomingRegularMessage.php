<?php

namespace SequentSoft\ThreadFlow\Messages\Incoming\Regular;

use DateTimeImmutable;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\AudioIncomingRegularMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\ContactIncomingRegularMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\FileIncomingRegularMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\ImageIncomingRegularMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\IncomingRegularMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\LocationIncomingRegularMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\StickerIncomingRegularMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\TextIncomingRegularMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\VideoIncomingRegularMessageInterface;
use SequentSoft\ThreadFlow\Messages\Incoming\IncomingMessage;

class IncomingRegularMessage extends IncomingMessage implements IncomingRegularMessageInterface
{
    protected string $text = '[message]';

    public function isText(?string $text = null): bool
    {
        if (! $this instanceof TextIncomingRegularMessageInterface) {
            return false;
        }

        return is_null($text) || $this->getText() === $text;
    }

    public function isTextContains(string $text): bool
    {
        if (! $this->isText()) {
            return false;
        }

        return str_contains($this->getText(), $text);
    }

    public function isTextRegex(string $pattern): bool
    {
        if (! $this->isText()) {
            return false;
        }

        return preg_match($pattern, $this->getText()) > 0;
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
        return $this instanceof LocationIncomingRegularMessageInterface;
    }

    public function isContact(): bool
    {
        return $this instanceof ContactIncomingRegularMessageInterface;
    }

    public function isFile(): bool
    {
        return $this instanceof FileIncomingRegularMessageInterface;
    }

    public function isSticker(): bool
    {
        return $this instanceof StickerIncomingRegularMessageInterface;
    }

    public function isVideo(): bool
    {
        return $this instanceof VideoIncomingRegularMessageInterface;
    }

    public function isImage(): bool
    {
        return $this instanceof ImageIncomingRegularMessageInterface;
    }

    public function isAudio(): bool
    {
        return $this instanceof AudioIncomingRegularMessageInterface;
    }
}

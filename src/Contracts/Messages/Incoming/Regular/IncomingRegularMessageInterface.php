<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular;

use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;

interface IncomingRegularMessageInterface extends IncomingMessageInterface
{
    public function getText(): string;

    public function setText(string $text): void;

    public function isText(?string $text = null): bool;

    public function isTextAndContains(string $text): bool;

    public function isTextAndMatch(string $expression): bool;

    public function isLocation(): bool;

    public function isSticker(): bool;

    public function isVideo(): bool;

    public function isImage(): bool;

    public function isAudio(): bool;

    public function isContact(): bool;

    public function isFile(): bool;
}

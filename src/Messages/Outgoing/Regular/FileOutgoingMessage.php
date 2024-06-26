<?php

namespace SequentSoft\ThreadFlow\Messages\Outgoing\Regular;

use SequentSoft\ThreadFlow\Contracts\Keyboard\BaseKeyboardInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\FileOutgoingMessageInterface;

class FileOutgoingMessage extends OutgoingMessage implements FileOutgoingMessageInterface
{
    protected bool $isFromUrl = false;

    final public function __construct(
        protected string $pathOrUrl,
        protected ?string $caption = null,
        BaseKeyboardInterface|array|null $keyboard = null,
    ) {
        $this->withKeyboard($keyboard);
    }

    public function isFromUrl(): bool
    {
        return $this->isFromUrl;
    }

    public function setFromUrl(bool $isFromUrl = true): static
    {
        $this->isFromUrl = $isFromUrl;

        return $this;
    }

    public static function makeFromUrl(string $url, ?string $caption = null): static
    {
        return (new static($url, $caption))->setFromUrl();
    }

    public static function makeFromPath(string $path, ?string $caption = null): static
    {
        return new static($path, $caption);
    }

    public function getPath(): ?string
    {
        return $this->isFromUrl ? null : $this->pathOrUrl;
    }

    public function getUrl(): ?string
    {
        return $this->isFromUrl ? $this->pathOrUrl : null;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }
}

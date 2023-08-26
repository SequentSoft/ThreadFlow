<?php

namespace SequentSoft\ThreadFlow\Messages\Outgoing\Regular;

use SequentSoft\ThreadFlow\Contracts\Keyboard\KeyboardInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\FileOutgoingRegularMessageInterface;

class FileOutgoingMessage extends OutgoingRegularMessage implements FileOutgoingRegularMessageInterface
{
    final public function __construct(
        protected string $url,
        protected ?string $caption = null,
        KeyboardInterface|array|null $keyboard = null,
    ) {
        $this->withKeyboard($keyboard);
    }

    public static function make(string $url, ?string $caption = null): static
    {
        return new static($url, $caption);
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }
}

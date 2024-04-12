<?php

namespace SequentSoft\ThreadFlow\Messages\Outgoing\Regular;

use SequentSoft\ThreadFlow\Contracts\Keyboard\BaseKeyboardInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\ImageOutgoingMessageInterface;

class ImageOutgoingMessage extends OutgoingMessage implements ImageOutgoingMessageInterface
{
    final public function __construct(
        protected string $url,
        protected ?string $caption = null,
        BaseKeyboardInterface|array|null $keyboard = null,
    ) {
        $this->withKeyboard($keyboard);
    }

    public static function make(string $url, ?string $caption = null): static
    {
        return new static($url, $caption);
    }

    public function getImageUrl(): string
    {
        return $this->url;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }
}

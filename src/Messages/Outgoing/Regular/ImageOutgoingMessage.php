<?php

namespace SequentSoft\ThreadFlow\Messages\Outgoing\Regular;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\KeyboardInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\MessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\ForwardOutgoingRegularMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\ImageOutgoingRegularMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\TextOutgoingRegularMessageInterface;

class ImageOutgoingMessage extends OutgoingRegularMessage implements ImageOutgoingRegularMessageInterface
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

    public function getImageUrl(): string
    {
        return $this->url;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }
}

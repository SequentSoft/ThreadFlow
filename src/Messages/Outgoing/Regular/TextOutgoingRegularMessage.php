<?php

namespace SequentSoft\ThreadFlow\Messages\Outgoing\Regular;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\KeyboardInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\TextOutgoingRegularMessageInterface;

class TextOutgoingRegularMessage extends OutgoingRegularMessage implements TextOutgoingRegularMessageInterface
{
    final public function __construct(
        protected string $text,
        KeyboardInterface|array|null $keyboard = null,
    ) {
        $this->withKeyboard($keyboard);
    }

    public function getText(): string
    {
        return $this->text;
    }

    public static function make(
        string $text,
        KeyboardInterface|array|null $keyboard = null,
    ): TextOutgoingRegularMessageInterface {
        return new static($text, $keyboard);
    }
}

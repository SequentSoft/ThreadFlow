<?php

namespace SequentSoft\ThreadFlow\Messages\Outgoing\Regular;

use SequentSoft\ThreadFlow\Contracts\Keyboard\KeyboardInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\TextOutgoingMessageInterface;

class TextOutgoingMessage extends OutgoingMessage implements TextOutgoingMessageInterface
{
    final public function __construct(
        protected string|array $text,
        KeyboardInterface|array|null $keyboard = null,
    ) {
        $this->withKeyboard($keyboard);
    }

    public function getLineSeparator(): string
    {
        return "\n";
    }

    public function getText(): string
    {
        if (is_array($this->text)) {
            return implode($this->getLineSeparator(), $this->text);
        }

        return $this->text;
    }

    public function setText(string|array $text): void
    {
        $this->text = $text;
    }

    public static function make(
        string|array $text,
        KeyboardInterface|array|null $keyboard = null,
    ): TextOutgoingMessageInterface {
        return new static($text, $keyboard);
    }
}

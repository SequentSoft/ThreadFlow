<?php

namespace SequentSoft\ThreadFlow\Messages\Outgoing\Regular;

use SequentSoft\ThreadFlow\Contracts\Keyboard\KeyboardInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\MarkdownOutgoingMessageInterface;

class MarkdownOutgoingMessage extends OutgoingMessage implements MarkdownOutgoingMessageInterface
{
    final public function __construct(
        protected string|array $markdown,
        KeyboardInterface|array|null $keyboard = null,
    ) {
        $this->withKeyboard($keyboard);
    }

    public function getLineSeparator(): string
    {
        return "\n";
    }

    public function getMarkdown(): string
    {
        if (is_array($this->markdown)) {
            return implode($this->getLineSeparator(), $this->markdown);
        }

        return $this->markdown;
    }

    public function setMarkdown(string|array $markdown): void
    {
        $this->markdown = $markdown;
    }

    public static function make(
        string|array $markdown,
        KeyboardInterface|array|null $keyboard = null,
    ): MarkdownOutgoingMessageInterface {
        return new static($markdown, $keyboard);
    }
}

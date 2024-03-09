<?php

namespace SequentSoft\ThreadFlow\Messages\Outgoing\Regular;

use SequentSoft\ThreadFlow\Contracts\Keyboard\KeyboardInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\HtmlOutgoingMessageInterface;

class HtmlOutgoingMessage extends OutgoingMessage implements HtmlOutgoingMessageInterface
{
    final public function __construct(
        protected string|array $html,
        KeyboardInterface|array|null $keyboard = null,
    ) {
        $this->withKeyboard($keyboard);
    }

    public function getLineSeparator(): string
    {
        return "\n";
    }

    public function getHtml(): string
    {
        if (is_array($this->html)) {
            return implode($this->getLineSeparator(), $this->html);
        }

        return $this->html;
    }

    public function setHtml(string|array $html): void
    {
        $this->html = $html;
    }

    public static function make(
        string|array $html,
        KeyboardInterface|array|null $keyboard = null,
    ): HtmlOutgoingMessageInterface {
        return new static($html, $keyboard);
    }
}

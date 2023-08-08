<?php

namespace SequentSoft\ThreadFlow\Messages\Outgoing\Regular;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\KeyboardInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\TextOutgoingRegularMessageInterface;

class TextOutgoingMessage extends OutgoingRegularMessage implements TextOutgoingRegularMessageInterface
{
    protected string $template = '';

    protected array $templateAttributes = [];

    final public function __construct(
        protected string $text,
        KeyboardInterface|array|null $keyboard = null,
    ) {
        $this->withKeyboard($keyboard);
    }

    public function getText(): string
    {
        return $this->text ?: $this->renderTemplateWithAttributes();
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function setTemplate(string $template): static
    {
        $this->template = $template;
        return $this;
    }

    public function setTemplateAttribute(string $name, string $value): static
    {
        $this->templateAttributes[$name] = $value;
        return $this;
    }

    public function getTemplateAttribute(string $name): string
    {
        return $this->templateAttributes[$name] ?? '';
    }

    protected function renderTemplateWithAttributes(): string
    {
        $template = $this->template;
        foreach ($this->templateAttributes as $name => $value) {
            $template = str_replace('{' . $name . '}', $value, $template);
        }
        return $template;
    }

    public static function makeWithTemplate(
        string $template,
        array $templateAttributes = [],
        KeyboardInterface|array|null $keyboard = null,
    ): TextOutgoingRegularMessageInterface {
        $message = new static('', $keyboard);
        $message->setTemplate($template);
        foreach ($templateAttributes as $name => $value) {
            $message->setTemplateAttribute($name, $value);
        }
        return $message;
    }

    public static function make(
        string $text,
        KeyboardInterface|array|null $keyboard = null,
    ): TextOutgoingRegularMessageInterface {
        return new static($text, $keyboard);
    }
}

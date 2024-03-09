<?php

namespace SequentSoft\ThreadFlow\Forms;

use SequentSoft\ThreadFlow\Contracts\Forms\FormFieldInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\OutgoingMessageInterface;

class TextFormField implements FormFieldInterface
{
    protected array $rules = [];

    protected array $messages = [];

    protected ?string $emptyButtonText = null;

    protected ?string $dontChangeButtonText = null;

    final public function __construct(
        protected string $key,
        protected ?string $caption = null,
        protected string|OutgoingMessageInterface|null $description = null,
    ) {
    }

    public static function make(
        string $key,
        ?string $caption = null,
        string|OutgoingMessageInterface|null $description = null
    ): static {
        return new static($key, $caption, $description);
    }

    public function rules(array $rules, array $messages = []): static
    {
        $this->rules = $rules;
        $this->messages = $messages;

        return $this;
    }

    public function caption(?string $caption): static
    {
        $this->caption = $caption;

        return $this;
    }

    public function description(string|OutgoingMessageInterface|null $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function emptyButtonText(string $text): static
    {
        $this->emptyButtonText = $text;

        return $this;
    }

    public function dontChangeButtonText(string $text): static
    {
        $this->dontChangeButtonText = $text;

        return $this;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function getDescription(): string|OutgoingMessageInterface|null
    {
        return $this->description;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getEmptyButtonText(): ?string
    {
        return $this->emptyButtonText;
    }

    public function getDontChangeButtonText(): ?string
    {
        return $this->dontChangeButtonText;
    }
}

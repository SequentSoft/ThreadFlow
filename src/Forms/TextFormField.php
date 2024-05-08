<?php

namespace SequentSoft\ThreadFlow\Forms;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Forms\FormFieldInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\OutgoingMessageInterface;

class TextFormField implements FormFieldInterface
{
    protected array $validationRules = [];

    protected array $validationMessages = [];

    protected array $validationAttributes = [];

    protected ?string $emptyButtonText = null;

    protected ?string $dontChangeButtonText = null;

    protected bool|Closure $disabled = false;

    protected ?Closure $onChangeCallback = null;

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

    public function disable($isDisabled = true): static
    {
        $this->disabled = $isDisabled;

        return $this;
    }

    public function isDisabled(): bool
    {
        if ($this->disabled instanceof Closure) {
            return (bool) call_user_func($this->disabled);
        }

        return $this->disabled;
    }

    public function onChange(Closure $callback): static
    {
        $this->onChangeCallback = $callback;

        return $this;
    }

    public function getOnChangeCallback(): ?Closure
    {
        return $this->onChangeCallback;
    }

    public function rules(array $rules, array $messages = [], array $attributes = []): static
    {
        $this->validationRules = $rules;
        $this->validationMessages = $messages;
        $this->validationAttributes = $attributes;

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

    public function getValidationRules(): array
    {
        return $this->validationRules;
    }

    public function getValidationMessages(): array
    {
        return $this->validationMessages;
    }

    public function getValidationAttributes(): array
    {
        return $this->validationAttributes;
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

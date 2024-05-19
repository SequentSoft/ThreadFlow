<?php

namespace SequentSoft\ThreadFlow\Forms;

use SequentSoft\ThreadFlow\Contracts\Forms\FormFieldOptionsInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\OutgoingMessageInterface;

class SelectFormField extends BaseFormField implements FormFieldOptionsInterface
{
    protected array $options = [];

    protected bool $customOptionAllowed = false;

    final public function __construct(
        string $key,
        ?string $caption = null,
        string|OutgoingMessageInterface|null $description = null,
        array $options = [],
    ) {
        $this->key = $key;
        $this->caption = $caption;
        $this->description = $description;
        $this->options = $options;
    }

    public static function make(
        string $key,
        ?string $caption = null,
        string|OutgoingMessageInterface|null $description = null,
        array $options = []
    ): static {
        return new static($key, $caption, $description, $options);
    }

    public function isCustomOptionAllowed(): bool
    {
        return $this->customOptionAllowed;
    }

    public function allowCustomOption(): static
    {
        $this->customOptionAllowed = true;

        return $this;
    }

    public function options(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}

<?php

namespace SequentSoft\ThreadFlow\Forms;

use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\OutgoingMessageInterface;

class TextFormField extends BaseFormField
{
    final public function __construct(
        string $key,
        ?string $caption = null,
        string|OutgoingMessageInterface|null $description = null,
    ) {
        $this->key = $key;
        $this->caption = $caption;
        $this->description = $description;
    }

    public static function make(
        string $key,
        ?string $caption = null,
        string|OutgoingMessageInterface|null $description = null
    ): static {
        return new static($key, $caption, $description);
    }
}

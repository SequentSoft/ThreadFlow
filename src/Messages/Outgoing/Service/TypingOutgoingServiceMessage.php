<?php

namespace SequentSoft\ThreadFlow\Messages\Outgoing\Service;

use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Service\TypingOutgoingServiceMessageInterface;
use SequentSoft\ThreadFlow\Enums\Messages\TypingType;

class TypingOutgoingServiceMessage extends OutgoingServiceMessage implements TypingOutgoingServiceMessageInterface
{
    final public function __construct(
        protected TypingType $type = TypingType::TYPING,
    ) {
    }

    public function getType(): TypingType
    {
        return $this->type;
    }

    public static function make(TypingType $type = TypingType::TYPING): static
    {
        return new static($type);
    }
}

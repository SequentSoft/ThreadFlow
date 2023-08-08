<?php

namespace SequentSoft\ThreadFlow\Messages\Outgoing\Regular;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\KeyboardInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\MessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\ForwardOutgoingRegularMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\TextOutgoingRegularMessageInterface;

class ForwardOutgoingMessage extends OutgoingRegularMessage implements ForwardOutgoingRegularMessageInterface
{
    final public function __construct(
        protected MessageInterface $message,
        KeyboardInterface|array|null $keyboard = null,
    ) {
        $this->withKeyboard($keyboard);
    }

    public function getTargetMessage(): MessageInterface
    {
        return $this->message;
    }

    public static function make(MessageInterface $message): static
    {
        return new static($message);
    }
}

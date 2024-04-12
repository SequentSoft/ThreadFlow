<?php

namespace SequentSoft\ThreadFlow\Messages\Outgoing\Regular;

use SequentSoft\ThreadFlow\Contracts\Keyboard\BaseKeyboardInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\MessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\ForwardOutgoingMessageInterface;

class ForwardOutgoingMessage extends OutgoingMessage implements ForwardOutgoingMessageInterface
{
    final public function __construct(
        protected MessageInterface $message,
        BaseKeyboardInterface|array|null $keyboard = null,
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

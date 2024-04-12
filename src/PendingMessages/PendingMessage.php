<?php

namespace SequentSoft\ThreadFlow\PendingMessages;

use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\PendingMessages\PendingMessageInterface;

class PendingMessage implements PendingMessageInterface
{
    protected mixed $message = null;

    public function __construct(mixed $message)
    {
        $this->message = $message;
    }

    public function isOutgoingMessage(): bool
    {
        return $this->message instanceof OutgoingMessageInterface;
    }

    public function isPage(): bool
    {
        return $this->message instanceof PageInterface;
    }

    public function getMessage(): OutgoingMessageInterface
    {
        if (! $this->isOutgoingMessage()) {
            throw new \RuntimeException('Message is not an instance of OutgoingMessageInterface');
        }

        return $this->message;
    }

    public function getPage(): PageInterface
    {
        if (! $this->isPage()) {
            throw new \RuntimeException('Message is not an instance of PageInterface');
        }

        return $this->message;
    }
}

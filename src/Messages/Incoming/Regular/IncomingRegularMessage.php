<?php

namespace SequentSoft\ThreadFlow\Messages\Incoming\Regular;

use DateTimeImmutable;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\IncomingRegularMessageInterface;
use SequentSoft\ThreadFlow\Messages\Incoming\IncomingMessage;

class IncomingRegularMessage extends IncomingMessage implements IncomingRegularMessageInterface
{
     protected string $text = '[message]';

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function isText(string $text): bool
    {
        return $this->getText() === $text;
    }
}

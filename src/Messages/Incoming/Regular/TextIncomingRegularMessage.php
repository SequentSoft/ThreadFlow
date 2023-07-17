<?php

namespace SequentSoft\ThreadFlow\Messages\Incoming\Regular;

use DateTimeImmutable;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\TextIncomingRegularMessageInterface;
use SequentSoft\ThreadFlow\Messages\Incoming\IncomingMessage;

class TextIncomingRegularMessage extends IncomingRegularMessage implements TextIncomingRegularMessageInterface
{
    final public function __construct(
        string $id,
        MessageContextInterface $context,
        DateTimeImmutable $timestamp,
        string $text,
    ) {
        parent::__construct($id, $context, $timestamp);

        $this->setText($text);
    }
}

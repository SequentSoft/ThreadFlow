<?php

namespace SequentSoft\ThreadFlow\Messages\Incoming\Regular;

use DateTimeImmutable;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\StickerIncomingRegularMessageInterface;

class StickerIncomingRegularMessage extends IncomingRegularMessage implements StickerIncomingRegularMessageInterface
{
    final public function __construct(
        string $id,
        MessageContextInterface $context,
        DateTimeImmutable $timestamp,
        protected ?string $url,
        protected ?string $name,
        protected ?string $stickerId,
        protected ?string $emoji,
    ) {
        parent::__construct($id, $context, $timestamp);

        $this->setText($emoji);
    }

    public function getStickerId(): string
    {
        return $this->stickerId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmoji(): string
    {
        return $this->emoji;
    }
}

<?php

namespace SequentSoft\ThreadFlow\Messages\Incoming\Regular;

use DateTimeImmutable;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\AudioIncomingRegularMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\FileIncomingRegularMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\ImageIncomingRegularMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\StickerIncomingRegularMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\VideoIncomingRegularMessageInterface;

class VideoIncomingRegularMessage extends IncomingRegularMessage implements VideoIncomingRegularMessageInterface
{
    final public function __construct(
        string $id,
        MessageContextInterface $context,
        DateTimeImmutable $timestamp,
        protected ?string $url,
        protected ?string $name,
    ) {
        parent::__construct($id, $context, $timestamp);

        $this->setText('');
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}

<?php

namespace SequentSoft\ThreadFlow\Messages\Incoming\Regular;

use DateTimeImmutable;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\FileIncomingRegularMessageInterface;

class FileIncomingRegularMessage extends IncomingRegularMessage implements FileIncomingRegularMessageInterface
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

<?php

namespace SequentSoft\ThreadFlow\Messages\Incoming\Regular;

use DateTimeImmutable;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\LocationIncomingMessageInterface;

class LocationIncomingMessage extends IncomingMessage implements LocationIncomingMessageInterface
{
    final public function __construct(
        string $id,
        ?MessageContextInterface $context,
        DateTimeImmutable $timestamp,
        protected float $latitude,
        protected float $longitude,
    ) {
        parent::__construct($id, $context, $timestamp);

        $this->setText($latitude.','.$longitude);
    }

    public static function make(
        float $latitude,
        float $longitude,
        ?string $id = null,
        ?MessageContextInterface $context = null,
        ?DateTimeImmutable $timestamp = null,
    ): static {
        return new static(
            $id ?? static::generateId(),
            $context,
            $timestamp ?? new DateTimeImmutable(),
            $latitude,
            $longitude
        );
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }
}

<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular;

interface ImageOutgoingMessageInterface extends OutgoingMessageInterface
{
    public function getImageUrl(): string;

    public function getCaption(): ?string;
}

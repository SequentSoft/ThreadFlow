<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular;

interface VideoOutgoingMessageInterface extends OutgoingMessageInterface
{
    public function getVideoUrl(): string;

    public function getCaption(): ?string;
}

<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular;

interface VideoOutgoingRegularMessageInterface extends OutgoingRegularMessageInterface
{
    public function getVideoUrl(): string;

    public function getCaption(): ?string;
}

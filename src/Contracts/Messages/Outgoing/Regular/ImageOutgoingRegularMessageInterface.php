<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular;

interface ImageOutgoingRegularMessageInterface extends OutgoingRegularMessageInterface
{
    public function getImageUrl(): string;
    public function getCaption(): ?string;
}

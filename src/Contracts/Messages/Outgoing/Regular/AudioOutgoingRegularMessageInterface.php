<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular;

interface AudioOutgoingRegularMessageInterface extends OutgoingRegularMessageInterface
{
    public function getAudioUrl(): string;
    public function getCaption(): ?string;
}

<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular;

interface AudioOutgoingMessageInterface extends OutgoingMessageInterface
{
    public function getAudioUrl(): string;

    public function getCaption(): ?string;
}

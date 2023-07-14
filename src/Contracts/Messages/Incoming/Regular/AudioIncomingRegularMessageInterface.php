<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular;

interface AudioIncomingRegularMessageInterface extends IncomingRegularMessageInterface
{
    public function getAudioUrl(): string;
    public function getCaption(): ?string;
}

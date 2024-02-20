<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular;

interface VideoIncomingMessageInterface extends IncomingMessageInterface
{
    public function getUrl(): ?string;

    public function getName(): ?string;
}

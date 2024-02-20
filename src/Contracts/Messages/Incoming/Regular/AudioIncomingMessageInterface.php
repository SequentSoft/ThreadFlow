<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular;

interface AudioIncomingMessageInterface extends IncomingMessageInterface
{
    public function getUrl(): ?string;

    public function getName(): ?string;
}

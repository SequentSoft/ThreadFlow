<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular;

interface FileIncomingMessageInterface extends IncomingMessageInterface
{
    public function getUrl(): ?string;

    public function getName(): ?string;
}

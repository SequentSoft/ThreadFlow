<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular;

interface FileOutgoingMessageInterface extends OutgoingMessageInterface
{
    public function getPath(): ?string;

    public function getUrl(): ?string;

    public function getCaption(): ?string;
}

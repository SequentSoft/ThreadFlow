<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular;

interface FileOutgoingRegularMessageInterface extends OutgoingRegularMessageInterface
{
    public function getPath(): ?string;

    public function getUrl(): ?string;

    public function getCaption(): ?string;
}

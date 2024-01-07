<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular;

interface FileIncomingRegularMessageInterface extends IncomingRegularMessageInterface
{
    public function getUrl(): ?string;

    public function getName(): ?string;
}

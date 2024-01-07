<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular;

interface AudioIncomingRegularMessageInterface extends IncomingRegularMessageInterface
{
    public function getUrl(): ?string;

    public function getName(): ?string;
}

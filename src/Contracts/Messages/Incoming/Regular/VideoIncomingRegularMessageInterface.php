<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular;

interface VideoIncomingRegularMessageInterface extends IncomingRegularMessageInterface
{
    public function getUrl(): ?string;
    public function getName(): ?string;
}

<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular;

interface LocationOutgoingMessageInterface extends OutgoingMessageInterface
{
    public function getLatitude(): float;

    public function getLongitude(): float;
}

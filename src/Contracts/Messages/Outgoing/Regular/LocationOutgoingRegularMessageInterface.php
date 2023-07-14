<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular;

interface LocationOutgoingRegularMessageInterface extends OutgoingRegularMessageInterface
{
    public function getLatitude(): float;
    public function getLongitude(): float;
}

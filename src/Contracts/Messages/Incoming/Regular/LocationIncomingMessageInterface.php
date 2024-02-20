<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular;

interface LocationIncomingMessageInterface extends IncomingMessageInterface
{
    public function getLatitude(): float;

    public function getLongitude(): float;
}

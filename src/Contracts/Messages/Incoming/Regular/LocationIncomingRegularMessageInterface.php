<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular;

interface LocationIncomingRegularMessageInterface extends IncomingRegularMessageInterface {
    public function getLatitude(): float;
    public function getLongitude(): float;
}

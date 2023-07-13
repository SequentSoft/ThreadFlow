<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular;

interface TextOutgoingRegularMessageInterface extends OutgoingRegularMessageInterface {
    public function getText(): string;
}

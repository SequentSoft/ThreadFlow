<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular;

use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\CommonOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\WithKeyboardInterface;

interface OutgoingMessageInterface extends CommonOutgoingMessageInterface, WithKeyboardInterface
{
}

<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular;

use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\BasicOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\WithKeyboardInterface;

interface OutgoingMessageInterface extends BasicOutgoingMessageInterface, WithKeyboardInterface
{
}

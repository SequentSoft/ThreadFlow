<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular;

use SequentSoft\ThreadFlow\Contracts\Forms\FormInterface;

interface FormResultMessageInterface extends IncomingMessageInterface
{
    public function getForm(): FormInterface;
}

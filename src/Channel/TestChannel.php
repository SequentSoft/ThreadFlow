<?php

namespace SequentSoft\ThreadFlow\Channel;

use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\BasicOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class TestChannel extends Channel
{
    protected function outgoing(
        BasicOutgoingMessageInterface $message,
        ?SessionInterface $session,
        ?PageInterface $contextPage
    ): BasicOutgoingMessageInterface {
        return $message;
    }
}

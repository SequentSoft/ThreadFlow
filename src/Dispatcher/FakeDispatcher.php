<?php

namespace SequentSoft\ThreadFlow\Dispatcher;

use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class FakeDispatcher extends SyncDispatcher
{
    public function outgoing(
        OutgoingMessageInterface $message,
        ?SessionInterface $session,
        ?PageInterface $page
    ): OutgoingMessageInterface {
        // fake sending message
        $message->setId(md5(random_bytes(32)));

        return $message;
    }
}

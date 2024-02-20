<?php

namespace SequentSoft\ThreadFlow\Dispatcher;

use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\CommonOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class FakeDispatcher extends SyncDispatcher
{
    public function outgoing(
        CommonOutgoingMessageInterface $message,
        ?SessionInterface              $session,
        ?PageInterface                 $page
    ): CommonOutgoingMessageInterface {
        // fake sending message
        $message->setId(
            md5(random_bytes(32))
        );

        return $message;
    }
}

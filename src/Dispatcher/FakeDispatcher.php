<?php

namespace SequentSoft\ThreadFlow\Dispatcher;

use Random\RandomException;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\CommonOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class FakeDispatcher extends SyncDispatcher
{
    /**
     * @throws RandomException
     */
    private function makeRandomId(): string
    {
        return md5(random_bytes(32));
    }

    /**
     * @throws RandomException
     */
    public function outgoing(
        CommonOutgoingMessageInterface $message,
        ?SessionInterface $session,
        ?PageInterface $page
    ): CommonOutgoingMessageInterface {
        $message->setId(
            $this->makeRandomId()
        );

        return $message;
    }
}

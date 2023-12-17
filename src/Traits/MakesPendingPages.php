<?php

namespace SequentSoft\ThreadFlow\Traits;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Events\ChannelEventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PendingDispatchPageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface as IMessageInterface;
use SequentSoft\ThreadFlow\Page\PendingDispatchPage;

trait MakesPendingPages
{
    protected function makePendingPage(
        string $channelName,
        ChannelEventBusInterface $eventBus,
        PageStateInterface $pageState,
        SessionInterface $session,
        MessageContextInterface $messageContext,
        ?IMessageInterface $message = null,
    ): PendingDispatchPageInterface {
        return new PendingDispatchPage(
            $channelName,
            $eventBus,
            $pageState,
            $session,
            $messageContext,
            $message,
        );
    }
}

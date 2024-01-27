<?php

namespace SequentSoft\ThreadFlow\Contracts\Page;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

interface PageFactoryInterface
{
    public function createPage(
        string $pageName,
        EventBusInterface $eventBus,
        PageStateInterface $state,
        SessionInterface $session,
        MessageContextInterface $messageContext,
        ?IncomingMessageInterface $message = null,
    ): PageInterface;
}

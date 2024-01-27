<?php

namespace SequentSoft\ThreadFlow\Laravel\Page;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class PageFactory implements PageFactoryInterface
{
    public function createPage(
        string $pageName,
        EventBusInterface $eventBus,
        PageStateInterface $state,
        SessionInterface $session,
        MessageContextInterface $messageContext,
        ?IncomingMessageInterface $message = null,
    ): PageInterface {
        return app()->make($pageName, [
            'eventBus' => $eventBus,
            'state' => $state,
            'session' => $session,
            'messageContext' => $messageContext,
            'message' => $message,
        ]);
    }
}

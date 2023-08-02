<?php

namespace SequentSoft\ThreadFlow\Contracts\Router;

use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

interface RouterInterface
{
    public function getCurrentPageState(
        IncomingMessageInterface $message,
        SessionInterface $session,
        string $fallbackClass
    ): PageStateInterface;

    public function setCurrentPageState(
        SessionInterface $session,
        PageStateInterface $pageState,
    ): void;
}

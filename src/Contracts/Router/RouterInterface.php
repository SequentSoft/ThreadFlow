<?php

namespace SequentSoft\ThreadFlow\Contracts\Router;

use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Router\PageClassWithAttributes;

interface RouterInterface
{
    public function getCurrentPage(
        IncomingMessageInterface $message,
        SessionInterface $session,
        string $fallbackClass
    ): PageClassWithAttributes;

    public function setCurrentPage(
        SessionInterface $session,
        string $class,
        array $attributes = [],
    ): void;
}

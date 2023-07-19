<?php

namespace SequentSoft\ThreadFlow\Router;

use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Router\RouterInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class StatefulPageRouter implements RouterInterface
{
    protected const SESSION_CURRENT_PAGE_CLASS_KEY = '$router:currentPageClass';
    protected const SESSION_CURRENT_PAGE_ATTRIBUTES_KEY = '$router:currentPageAttributes';
    protected const SESSION_CURRENT_PAGE_BREADCRUMBS_KEY = '$router:breadcrumbs';

    public function getCurrentPage(
        IncomingMessageInterface $message,
        SessionInterface $session,
        string $fallbackClass
    ): PageClassWithAttributes {
        return new PageClassWithAttributes(
            $session->get(self::SESSION_CURRENT_PAGE_CLASS_KEY, $fallbackClass),
            $session->get(self::SESSION_CURRENT_PAGE_ATTRIBUTES_KEY, []),
            $session->get(self::SESSION_CURRENT_PAGE_BREADCRUMBS_KEY, []),
            is_null($session->get(self::SESSION_CURRENT_PAGE_CLASS_KEY)),
        );
    }

    public function setCurrentPage(
        SessionInterface $session,
        string $class,
        array $attributes = [],
        array $breadcrumbs = [],
    ): void {
        $session->set(self::SESSION_CURRENT_PAGE_CLASS_KEY, $class);
        $session->set(self::SESSION_CURRENT_PAGE_ATTRIBUTES_KEY, $attributes);
        $session->set(self::SESSION_CURRENT_PAGE_BREADCRUMBS_KEY, $breadcrumbs);
    }
}

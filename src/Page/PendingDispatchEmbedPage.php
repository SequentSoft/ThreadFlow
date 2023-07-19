<?php

namespace SequentSoft\ThreadFlow\Page;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Router\RouterInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class PendingDispatchEmbedPage extends PendingDispatchPage
{
    public function dispatch(?AbstractPage $contextPage, Closure $callback): AbstractPage
    {
        $nestedSession = $this->session->nested('$embed');

        $pageClassWithAttributes = $this->router->getCurrentPage(
            $this->message,
            $nestedSession,
            $this->pageClass
        );

        $pageClass = $pageClassWithAttributes->getPageClass();

        $pageAttributes = $pageClassWithAttributes->isFallback()
            ? $this->attributes
            : $pageClassWithAttributes->getAttributes();

        $page = new $pageClass(
            $pageAttributes,
            $nestedSession,
            $this->message,
            $this->router,
        );

        foreach ($this->pageEvents as $eventName => $pageEvents) {
            $page->on($eventName, $pageEvents);
        }

        $next = $page->execute($callback);

        if ($next instanceof PendingDispatchPage) {
            foreach ($this->pageEvents as $eventName => $pageEvents) {
                $next->on($eventName, $pageEvents);
            }

            $next->dispatch($page, $callback);
        }

        $nestedSession->close();

        return $page;
    }
}

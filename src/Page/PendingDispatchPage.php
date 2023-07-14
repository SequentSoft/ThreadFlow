<?php

namespace SequentSoft\ThreadFlow\Page;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Router\RouterInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class PendingDispatchPage
{
    protected array $pageEvents = [];

    public function __construct(
        protected string $pageClass,
        protected array $attributes,
        protected SessionInterface $session,
        protected IncomingMessageInterface $message,
        protected RouterInterface $router,
    ) {
    }

    public function getPageClass(): string
    {
        return $this->pageClass;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function on(string $eventName, Closure $callback): static
    {
        $this->pageEvents[$eventName][] = $callback;

        return $this;
    }

    public function dispatch(Closure $callback): AbstractPage
    {
        $page = new $this->pageClass(
            $this->attributes,
            $this->session,
            $this->message,
            $this->router,
        );

        foreach ($this->pageEvents as $eventName => $pageEvents) {
            $page->on($eventName, $pageEvents);
        }

        $next = $page->execute($callback);

        if ($next instanceof static) {
            foreach ($this->pageEvents as $eventName => $pageEvents) {
                $page->on($eventName, $pageEvents);
            }

            $next->dispatch($callback);
        }

        return $page;
    }
}

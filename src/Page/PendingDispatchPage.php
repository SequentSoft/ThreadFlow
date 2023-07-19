<?php

namespace SequentSoft\ThreadFlow\Page;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Router\RouterInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class PendingDispatchPage
{
    protected array $pageEvents = [];

    protected array $breadcrumbs = [];

    protected bool $withBreadcrumbs = false;

    protected ?bool $withBreadcrumbsReplace = null;

    public function __construct(
        protected string $pageClass,
        protected array $attributes,
        protected SessionInterface $session,
        protected IncomingMessageInterface $message,
        protected RouterInterface $router,
    ) {
    }

    public function setBreadcrumbs(array $breadcrumbs): static
    {
        $this->breadcrumbs = $breadcrumbs;

        return $this;
    }

    public function getPageClass(): string
    {
        return $this->pageClass;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function withBreadcrumbs(): static
    {
        $this->withBreadcrumbs = true;
        $this->withBreadcrumbsReplace = false;

        return $this;
    }

    public function withBreadcrumbsReplace(): static
    {
        $this->withBreadcrumbs = true;
        $this->withBreadcrumbsReplace = true;

        return $this;
    }

    public function on(string $eventName, Closure $callback): static
    {
        $this->pageEvents[$eventName][] = $callback;

        return $this;
    }

    public function dispatch(?AbstractPage $contextPage, Closure $callback): AbstractPage
    {
        /** @var AbstractPage $page */
        $page = new $this->pageClass(
            $this->attributes,
            $this->session,
            $this->message,
            $this->router,
        );

        if ($this->withBreadcrumbs) {
            $breadcrumbs = $this->breadcrumbs;
            if (! $this->withBreadcrumbsReplace && $contextPage) {
                $breadcrumbs[] = new Breadcrumb(get_class($contextPage), $contextPage->getAttributes());
            }
            $page->setBreadcrumbs($breadcrumbs);
        }

        foreach ($this->pageEvents as $eventName => $pageEvents) {
            $page->on($eventName, $pageEvents);
        }

        $next = $page->execute($callback);

        if ($next instanceof static) {
            foreach ($this->pageEvents as $eventName => $pageEvents) {
                $page->on($eventName, $pageEvents);
            }

            $next->dispatch($page, $callback);
        }

        return $page;
    }
}

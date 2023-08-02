<?php

namespace SequentSoft\ThreadFlow\Page;

use Closure;
use Exception;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PendingDispatchPageInterface;
use SequentSoft\ThreadFlow\Contracts\Router\RouterInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class PendingDispatchPage implements PendingDispatchPageInterface
{
    protected bool $keepAliveContextPage = false;

    protected bool $withBreadcrumbs = false;

    protected ?bool $withBreadcrumbsReplace = null;

    public function __construct(
        protected PageStateInterface $state,
        protected SessionInterface $session,
        protected MessageContextInterface $messageContext,
        protected ?IncomingMessageInterface $message,
    ) {
    }

    public function keepAliveCurrentPage(): static
    {
        $this->keepAliveContextPage = true;

        return $this;
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

    public function dispatch(?PageInterface $contextPage, Closure $callback): PageInterface
    {
        if ($this->keepAliveContextPage && $contextPage) {
            $this->session->getBackgroundPageStates()->set($contextPage->getState());
        }

        if ($contextPage) {
            $this->session->setPageState($this->state);
            $this->session->getBackgroundPageStates()->remove($this->state->getId());
        }

        $pageClass = $this->state->getPageClass();

        if (is_null($pageClass)) {
            throw new Exception('Page class is not defined');
        }

        /** @var PageInterface $page */
        $page = new $pageClass(
            $this->state,
            $this->session,
            $this->messageContext,
            $this->message,
        );

        if ($this->withBreadcrumbs) {
            if (! $this->withBreadcrumbsReplace && $contextPage) {
                $this->session->getBreadcrumbs()->push($contextPage->getState());
            }
        } else {
            $this->session->getBreadcrumbs()->clear();
        }

        $next = $page->execute($callback);

        if ($next instanceof static) {
            $next->dispatch($page, $callback);
        }

        return $page;
    }
}

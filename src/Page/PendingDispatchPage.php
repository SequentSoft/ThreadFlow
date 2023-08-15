<?php

namespace SequentSoft\ThreadFlow\Page;

use Closure;
use Exception;
use RuntimeException;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Events\ChannelEventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PendingDispatchInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PendingDispatchPageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Events\Page\PageDispatchedEvent;
use SequentSoft\ThreadFlow\Events\Page\PageDispatchingEvent;

class PendingDispatchPage implements PendingDispatchPageInterface
{
    protected bool $keepAliveContextPage = false;

    protected bool $withBreadcrumbs = false;

    protected ?bool $withBreadcrumbsReplace = null;

    public function __construct(
        protected string $channelName,
        protected ChannelEventBusInterface $eventBus,
        protected PageStateInterface $state,
        protected SessionInterface $session,
        protected MessageContextInterface $messageContext,
        protected ?IncomingMessageInterface $message,
    ) {
    }

    public function withMessage(?IncomingMessageInterface $message): static
    {
        $this->message = $message;

        return $this;
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

    /**
     * @throws Exception
     */
    public function dispatch(?PageInterface $contextPage = null, ?Closure $callback = null): PageInterface
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
            throw new RuntimeException('Page class is not defined');
        }

        /** @var PageInterface $page */
        $page = new $pageClass(
            $this->channelName,
            $this->eventBus,
            $this->state,
            $this->session,
            $this->messageContext,
            $this->message,
        );

        $this->eventBus->fire(
            new PageDispatchingEvent($this, $page, $contextPage)
        );

        if ($this->withBreadcrumbs) {
            if (! $this->withBreadcrumbsReplace && $contextPage) {
                $this->session->getBreadcrumbs()->push($contextPage->getState());
            }
        } else {
            $this->session->getBreadcrumbs()->clear();
        }

        $next = $page->execute($callback);

        $this->eventBus->fire(
            new PageDispatchedEvent($this, $page, $contextPage)
        );

        if ($next instanceof PendingDispatchInterface) {
            $next->dispatch($page, $callback);
        }

        return $page;
    }
}

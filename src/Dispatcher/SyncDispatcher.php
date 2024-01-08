<?php

namespace SequentSoft\ThreadFlow\Dispatcher;

use Closure;
use RuntimeException;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PendingDispatchPageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Enums\State\BreadcrumbsType;
use SequentSoft\ThreadFlow\Events\Message\OutgoingMessageSendingEvent;
use SequentSoft\ThreadFlow\Events\Message\OutgoingMessageSentEvent;
use SequentSoft\ThreadFlow\Events\Page\PageDispatchedEvent;
use SequentSoft\ThreadFlow\Events\Page\PageDispatchingEvent;
use SequentSoft\ThreadFlow\Session\PageState;

class SyncDispatcher implements DispatcherInterface
{
    public function __construct(
        protected string $channelName,
        protected EventBusInterface $eventBus,
        protected ConfigInterface $config,
        protected Closure $outgoingCallback,
    ) {
    }

    protected function makePage(
        PageStateInterface $nextState,
        SessionInterface $session,
        MessageContextInterface $messageContext,
        ?IncomingMessageInterface $message = null,
    ): PageInterface {
        $pageClass = $nextState->getPageClass();

        if (is_null($pageClass)) {
            throw new RuntimeException('Page class is not defined');
        }

        return new $pageClass(
            $this->channelName,
            $this->eventBus,
            $nextState,
            $session,
            $messageContext,
            $message,
        );
    }

    public function outgoing(
        OutgoingMessageInterface $message,
        ?SessionInterface $session,
        ?PageInterface $page
    ): OutgoingMessageInterface {
        return call_user_func($this->outgoingCallback, $message, $session, $page);
    }

    protected function executePage(
        PageInterface $page,
        SessionInterface $session,
        MessageContextInterface $messageContext,
        ?PageInterface $contextPage = null,
    ): void {
        $this->eventBus->fire(
            new PageDispatchingEvent($page, $contextPage)
        );

        $next = $page->execute(function (OutgoingMessageInterface $message) use ($session, $page) {
            $this->eventBus->fire(new OutgoingMessageSendingEvent($message, $session, $page));
            $message = $this->outgoing($message, $session, $page);
            $this->eventBus->fire(new OutgoingMessageSentEvent($message, $session, $page));

            return $message;
        });

        $this->eventBus->fire(
            new PageDispatchedEvent($page, $contextPage)
        );

        if ($next instanceof PendingDispatchPageInterface) {
            $this->transition(
                $messageContext,
                $session,
                $next,
                $page,
            );
        }
    }

    protected function getNextState(
        SessionInterface $session,
        PendingDispatchPageInterface $pendingDispatchPage
    ): PageStateInterface {
        $state = $pendingDispatchPage->getStateId()
            ? $session->getBackgroundPageStates()->get($pendingDispatchPage->getStateId())
            : null;

        return $state ?? PageState::create(
            $pendingDispatchPage->getPage(),
            $pendingDispatchPage->getAttributes(),
        );
    }

    public function transition(
        MessageContextInterface $messageContext,
        SessionInterface $session,
        PendingDispatchPageInterface $pendingDispatchPage,
        ?PageInterface $contextPage = null
    ): void {
        if ($contextPage && $pendingDispatchPage->isKeepAliveContextPage()) {
            $session->getBackgroundPageStates()->set($contextPage->getState());
        }

        $nextState = $this->getNextState($session, $pendingDispatchPage);
        $session->setPageState($nextState);
        $session->getBackgroundPageStates()->remove($nextState->getId());

        $page = $this->makePage(
            $nextState,
            $session,
            $messageContext,
        );

        if ($pendingDispatchPage->getBreadcrumbsType() === BreadcrumbsType::None) {
            $session->getBreadcrumbs()->clear();
        } elseif ($contextPage && $pendingDispatchPage->getBreadcrumbsType() === BreadcrumbsType::Append) {
            $session->getBreadcrumbs()->push($contextPage->getState());
        }

        $this->executePage(
            $page,
            $session,
            $messageContext,
            $contextPage,
        );
    }

    public function incoming(
        IncomingMessageInterface $message,
        SessionInterface $session
    ): void {
        $pageState = $this->getCurrentPageState($message, $session);

        $page = $this->makePage(
            $pageState,
            $session,
            $message->getContext(),
            $message,
        );

        $this->executePage($page, $session, $message->getContext());
    }

    protected function getCurrentPageState(
        IncomingMessageInterface $message,
        SessionInterface $session,
    ): PageStateInterface {
        $stateId = $message->getStateId();

        if ($stateId) {
            $backgroundPageState = $session->getBackgroundPageStates()->get($stateId);

            if ($backgroundPageState) {
                return $backgroundPageState;
            }
        }

        $state = $session->getPageState();

        if (is_null($state->getPageClass())) {
            $state->setPageClass($this->config->get('entry'));
        }

        return $state;
    }
}

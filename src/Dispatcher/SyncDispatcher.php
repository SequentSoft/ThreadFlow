<?php

namespace SequentSoft\ThreadFlow\Dispatcher;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\CommonIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\CommonOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Events\Message\OutgoingMessageSendingEvent;
use SequentSoft\ThreadFlow\Events\Message\OutgoingMessageSentEvent;
use SequentSoft\ThreadFlow\Events\Page\PageDispatchedEvent;
use SequentSoft\ThreadFlow\Events\Page\PageDispatchingEvent;
use SequentSoft\ThreadFlow\Page\AnswerToPage;

class SyncDispatcher implements DispatcherInterface
{
    public function __construct(
        protected EventBusInterface $eventBus,
        protected ConfigInterface $config,
        protected Closure $outgoingCallback,
    ) {
    }

    /**
     * Handle all outgoing messages.
     */
    public function outgoing(
        CommonOutgoingMessageInterface $message,
        ?SessionInterface $session,
        ?PageInterface $page
    ): CommonOutgoingMessageInterface {
        return call_user_func($this->outgoingCallback, $message, $session, $page);
    }

    /**
     * Execute the page and handle the result.
     * If the result is a page, it will be executed and transitioned to.
     */
    protected function executePage(
        PageInterface $page,
        SessionInterface $session,
        MessageContextInterface $messageContext,
        ?CommonIncomingMessageInterface $message,
        ?PageInterface $contextPage = null,
    ): void {
        $this->eventBus->fire(
            new PageDispatchingEvent($page, $contextPage)
        );

        $next = $page->execute(
            $this->eventBus,
            $message,
            function (CommonOutgoingMessageInterface $message) use ($session, $page) {
                $this->eventBus->fire(new OutgoingMessageSendingEvent($message, $session, $page));
                $message = $this->outgoing($message, $session, $page);
                $this->eventBus->fire(new OutgoingMessageSentEvent($message, $session, $page));

                return $message;
            }
        );

        $session->setCurrentPage($page);

        $this->eventBus->fire(
            new PageDispatchedEvent($page, $contextPage)
        );

        if ($next instanceof AnswerToPage) {
            $this->processResultAnswerToPage($page, $session, $messageContext, $next);
            $this->processPendingInteractions($session, $messageContext, $next->getPage());
            return;
        }

        if ($next instanceof PageInterface) {
            $this->processResultPage($page, $session, $messageContext, $next);
            $this->processPendingInteractions($session, $messageContext, $next);
            return;
        }

        if (! is_null($next)) {
            throw new \RuntimeException('The page result is not supported.');
        }
    }

    protected function processResultAnswerToPage(
        PageInterface $page,
        SessionInterface $session,
        MessageContextInterface $messageContext,
        AnswerToPage $result
    ): void {
        $result->getPage()->setContext($messageContext)->setSession($session);
        $session->setCurrentPage($result->getPage());
        $this->setupPrevPage($result->getPage(), $page);
        $this->executePage($result->getPage(), $session, $messageContext, $result->getMessage(), $page);
    }

    protected function processResultPage(
        PageInterface $page,
        SessionInterface $session,
        MessageContextInterface $messageContext,
        PageInterface $result
    ): void {
        $result->setContext($messageContext)->setSession($session);
        $this->transition($messageContext, $session, $result, $page);
    }

    protected function processPendingInteractions(
        SessionInterface $session,
        MessageContextInterface $messageContext,
        PageInterface $result
    ): void {
        if (! $session->hasPendingInteractions()) {
            return;
        }

        while (! $result->isDontDisturb() && $interaction = $session->takePendingInteraction()) {
            if ($interaction instanceof PageInterface) {
                $interaction->setContext($messageContext)->setSession($session);
                $this->transition($messageContext, $session, $interaction, $result);
            } else {
                $this->outgoing($interaction, $session, null);
            }
        }
    }

    protected function setupPrevPage(
        PageInterface $page,
        ?PageInterface $contextPage
    ): void {
        // clean up the prev page if it's not trackable
        if (! $contextPage?->isTrackingPrev()) {
            $contextPage?->setPrev(null);
        }

        if (! $page->getPrev()) {
            $page->setPrev($contextPage);
        }
    }

    /**
     * Transition to the new page.
     * If the context page is not trackable, it will be cleaned up.
     */
    public function transition(
        MessageContextInterface $messageContext,
        SessionInterface $session,
        PageInterface $page,
        ?PageInterface $contextPage = null
    ): void {
        $session->setCurrentPage($page);
        $this->setupPrevPage($page, $contextPage);
        $this->executePage($page, $session, $messageContext, null, $contextPage);
    }

    /**
     * Handle all incoming messages.
     */
    public function incoming(
        CommonIncomingMessageInterface $message,
        SessionInterface $session
    ): void {
        $page = $session->getCurrentPage();

        if (! $page) {
            $pageClass = $this->config->get('entry');
            $page = new $pageClass();
        }

        $page
            ->setContext($message->getContext())
            ->setSession($session);

        $this->executePage($page, $session, $message->getContext(), $message);
    }
}

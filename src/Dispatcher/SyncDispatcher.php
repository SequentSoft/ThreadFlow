<?php

namespace SequentSoft\ThreadFlow\Dispatcher;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\BasicIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\BasicOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\ActivePagesRepositoryInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\PendingMessages\PendingMessagesRepositoryInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Events\Message\IncomingMessageDispatchingEvent;
use SequentSoft\ThreadFlow\Events\Message\OutgoingMessageSendingEvent;
use SequentSoft\ThreadFlow\Events\Message\OutgoingMessageSentEvent;
use SequentSoft\ThreadFlow\Events\Page\PageDispatchedEvent;
use SequentSoft\ThreadFlow\Events\Page\PageDispatchingEvent;
use SequentSoft\ThreadFlow\Page\Responses\AnswerToPage;

class SyncDispatcher implements DispatcherInterface
{
    protected Closure $outgoingCallback;

    public function __construct(
        protected EventBusInterface $eventBus,
        protected ActivePagesRepositoryInterface $activePagesRepository,
        protected PendingMessagesRepositoryInterface $pendingMessagesRepository,
    ) {
    }

    public function setOutgoingCallback(Closure $outgoingCallback): void
    {
        $this->outgoingCallback = $outgoingCallback;
    }

    /**
     * Handle all outgoing messages.
     */
    public function outgoing(
        BasicOutgoingMessageInterface $message,
        ?SessionInterface $session,
        ?PageInterface $page
    ): BasicOutgoingMessageInterface {
        return call_user_func($this->outgoingCallback, $message, $session, $page);
    }

    protected function destroyActivePage(
        MessageContextInterface $messageContext,
        SessionInterface $session,
        array $destroyPageIds,
        array $whitelistPageIds,
    ): void {
        foreach (array_diff($destroyPageIds, $whitelistPageIds) as $destroyPageId) {
            if ($prevPageId = $this->activePagesRepository->getPrevId($messageContext, $session, $destroyPageId)) {
                $this->destroyActivePage(
                    $messageContext,
                    $session,
                    array_filter([$prevPageId]),
                    array_merge($whitelistPageIds, [$destroyPageId])
                );
            }

            $this->activePagesRepository->delete($messageContext, $session, $destroyPageId);
        }
    }

    /**
     * Execute the page and handle the result.
     * If the result is a page, it will be executed and transitioned to.
     */
    protected function executePage(
        PageInterface $page,
        SessionInterface $session,
        MessageContextInterface $messageContext,
        ?BasicIncomingMessageInterface $message,
        ?PageInterface $contextPage = null,
    ): void {
        $this->eventBus->fire(
            new PageDispatchingEvent($page, $contextPage)
        );

        $next = $page->execute(
            $this->eventBus,
            $message,
            function (BasicOutgoingMessageInterface $message) use ($session, $page) {
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
        $result->getPage()
            ->setContext($messageContext)
            ->setActivePagesRepository($this->activePagesRepository)
            ->setSession($session);
        $session->setCurrentPage($result->getPage());
        $this->setupPrevPage($messageContext, $session, $result->getPage(), $page);
        $this->executePage($result->getPage(), $session, $messageContext, $result->getMessage(), $page);
    }

    protected function processResultPage(
        PageInterface $page,
        SessionInterface $session,
        MessageContextInterface $messageContext,
        PageInterface $resultPage
    ): void {
        $resultPage
            ->setContext($messageContext)
            ->setActivePagesRepository($this->activePagesRepository)
            ->setSession($session);
        $this->transition($messageContext, $session, $resultPage, $page);
    }

    public function pushPendingMessage(
        MessageContextInterface $messageContext,
        SessionInterface $session,
        PageInterface|BasicOutgoingMessageInterface $pageOrMessage
    ): void {
        if ($pageOrMessage instanceof PageInterface) {
            $this->pendingMessagesRepository->pushTransitionToPage(
                $messageContext,
                $session,
                $pageOrMessage
            );

            return;
        }

        if ($pageOrMessage instanceof OutgoingMessageInterface) {
            $this->pendingMessagesRepository->pushOutgoingMessage(
                $messageContext,
                $session,
                $pageOrMessage
            );

            return;
        }

        throw new \RuntimeException(
            'The page or message is not supported. Class: ' . get_class($pageOrMessage)
        );
    }

    protected function processPendingInteractions(
        SessionInterface $session,
        MessageContextInterface $messageContext,
        PageInterface $result
    ): void {
        if (! $result->isDontDisturb() && $this->pendingMessagesRepository->isEmpty($messageContext, $session)) {
            return;
        }

        while (! $result->isDontDisturb()
            && $pendingMessage = $this->pendingMessagesRepository->pull($messageContext, $session)) {
            if ($pendingMessage->isPage()) {
                $page = $pendingMessage->getPage();
                $page
                    ->setContext($messageContext)
                    ->setActivePagesRepository($this->activePagesRepository)
                    ->setSession($session);
                $this->transition($messageContext, $session, $page, $result);
            }

            if ($pendingMessage->isOutgoingMessage()) {
                $this->outgoing($pendingMessage->getMessage(), $session, null);
            }
        }
    }

    protected function setupPrevPage(
        MessageContextInterface $messageContext,
        SessionInterface $session,
        PageInterface $page,
        ?PageInterface $contextPage
    ): void {
        if (! $contextPage) {
            return;
        }

        $whitelist = array_filter([
            $page->getId(),
            $page->getPrevPageId(),
        ]);

        if ($page->getPrevPageId()) {
            $this->destroyActivePage($messageContext, $session, [$contextPage->getId()], $whitelist);

            return;
        }

        if (! $contextPage->isTrackingPrev() && $contextPage->getPrevPageId()) {
            $this->destroyActivePage($messageContext, $session, [$contextPage->getPrevPageId()], $whitelist);
            $contextPage->setPrevPageId(null);
        }

        $this->activePagesRepository->put($messageContext, $session, $contextPage);
        $page->setPrevPageId($contextPage->getId());
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
        $this->setupPrevPage($messageContext, $session, $page, $contextPage);
        $this->executePage($page, $session, $messageContext, null, $contextPage);
    }

    /**
     * Handle all incoming messages.
     */
    public function incoming(
        BasicIncomingMessageInterface $message,
        SessionInterface $session,
        PageInterface $page,
    ): void {
        $page
            ->setContext($message->getContext())
            ->setActivePagesRepository($this->activePagesRepository)
            ->setSession($session);

        $this->eventBus->fire(new IncomingMessageDispatchingEvent($message, $page, $session));

        $this->executePage($page, $session, $message->getContext(), $message);
    }
}

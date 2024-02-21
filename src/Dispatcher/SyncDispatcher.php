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
use SequentSoft\ThreadFlow\Events\Page\PageHandleDelegatedEvent;
use SequentSoft\ThreadFlow\Events\Page\PageHasNoMessageHandlerEvent;
use SequentSoft\ThreadFlow\Exceptions\Page\MessageHandlerNotDeclaredException;

class SyncDispatcher implements DispatcherInterface
{
    public function __construct(
        protected EventBusInterface $eventBus,
        protected ConfigInterface $config,
        protected Closure $outgoingCallback,
    ) {
    }

    public function outgoing(
        CommonOutgoingMessageInterface $message,
        ?SessionInterface              $session,
        ?PageInterface                 $page
    ): CommonOutgoingMessageInterface {
        return call_user_func($this->outgoingCallback, $message, $session, $page);
    }

    private function tryExecutePage(
        PageInterface                   $page,
        SessionInterface                $session,
        ?CommonIncomingMessageInterface $message,
    ): ?PageInterface {
        try {
            return $page->execute(
                $this->eventBus,
                $message,
                function (CommonOutgoingMessageInterface $message) use ($session, $page) {
                    $this->eventBus->fire(new OutgoingMessageSendingEvent($message, $session, $page));
                    $message = $this->outgoing($message, $session, $page);
                    $this->eventBus->fire(new OutgoingMessageSentEvent($message, $session, $page));

                    return $message;
                }
            );
        } catch (MessageHandlerNotDeclaredException $exception) {
            $this->handleNotHandledMessage(
                $exception,
                $session,
                $message,
            );

            return null;
        }
    }

    protected function executePage(
        PageInterface                   $page,
        SessionInterface                $session,
        MessageContextInterface         $messageContext,
        ?CommonIncomingMessageInterface $message,
        ?PageInterface                  $contextPage = null,
    ): void {
        $this->eventBus->fire(
            new PageDispatchingEvent($page, $contextPage)
        );

        $next = $this->tryExecutePage($page, $session, $message);

        $session->setCurrentPage($page);

        $this->eventBus->fire(
            new PageDispatchedEvent($page, $contextPage)
        );

        $this->processPageExecutionResult($page, $session, $messageContext, $next);
    }

    protected function processPageExecutionResult(
        PageInterface $page,
        SessionInterface $session,
        MessageContextInterface $messageContext,
        mixed $result
    ): void {
        if (! $result instanceof PageInterface) {
            return;
        }

        $result
            ->setContext($messageContext)
            ->setSession($session);

        $this->transition($messageContext, $session, $result, $page);

        if ($session->hasPendingInteractions()) {
            while (! $result->isDontDisturb() && $interaction = $session->takePendingInteraction()) {
                if ($interaction instanceof PageInterface) {
                    $interaction
                        ->setContext($messageContext)
                        ->setSession($session);

                    $this->transition($messageContext, $session, $interaction, $result);
                } else {
                    $this->outgoing($interaction, $session, null);
                }
            }
        }
    }

    protected function handleNotHandledMessage(
        MessageHandlerNotDeclaredException $exception,
        SessionInterface                   $session,
        ?CommonIncomingMessageInterface    $message,
    ): void {
        $fromPage = $exception->getPage();

        if ($exception->getHandlerType() !== MessageHandlerNotDeclaredException::TYPE_SERVICE) {
            $this->eventBus->fire(
                new PageHasNoMessageHandlerEvent($fromPage)
            );

            return;
        }

        $pageClass = $this->config->get('entry');
        $toPage = new $pageClass();

        // pass the service message to the entry page
        $this->executePage(
            $toPage,
            $session,
            $fromPage->getContext(),
            $message,
            $fromPage,
        );

        $this->eventBus->fire(
            new PageHandleDelegatedEvent($fromPage, $toPage)
        );
    }

    public function transition(
        MessageContextInterface $messageContext,
        SessionInterface $session,
        PageInterface $page,
        ?PageInterface $contextPage = null
    ): void {
        $session->setCurrentPage($page);

        // clean up the prev page if it's not trackable
        if (! $contextPage?->isTrackingPrev()) {
            $contextPage?->setPrev(null);
        }

        if (! $page->getPrev()) {
            $page->setPrev($contextPage);
        }

        $this->executePage(
            $page,
            $session,
            $messageContext,
            null,
            $contextPage,
        );
    }

    public function incoming(
        CommonIncomingMessageInterface $message,
        SessionInterface               $session
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

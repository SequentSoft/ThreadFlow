<?php

declare(strict_types=1);

namespace SequentSoft\ThreadFlow;

use Closure;
use Exception;
use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Incoming\IncomingChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Outgoing\OutgoingChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\SimpleConfigInterface;
use SequentSoft\ThreadFlow\Contracts\DataFetchers\DataFetcherInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Events\ChannelEventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface as IMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface as OMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Router\RouterInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;
use SequentSoft\ThreadFlow\Events\Message\IncomingMessageDispatchingEvent;
use SequentSoft\ThreadFlow\Events\Message\IncomingMessageProcessingEvent;
use SequentSoft\ThreadFlow\Events\Message\OutgoingMessageEmittedEvent;
use SequentSoft\ThreadFlow\Events\Message\OutgoingMessageSendingEvent;
use SequentSoft\ThreadFlow\Events\Message\OutgoingMessageSentEvent;
use SequentSoft\ThreadFlow\Messages\Outgoing\Regular\TextOutgoingMessage;
use SequentSoft\ThreadFlow\Page\PendingDispatchPage;
use SequentSoft\ThreadFlow\Session\PageState;
use Throwable;

class ChannelBot implements BotInterface
{
    protected array $processingExceptionsHandlers = [];

    public function __construct(
        protected string $channelName,
        protected SimpleConfigInterface $config,
        protected SessionStoreInterface $sessionStore,
        protected RouterInterface $router,
        protected OutgoingChannelInterface $outgoingChannel,
        protected IncomingChannelInterface $incomingChannel,
        protected DispatcherInterface $dispatcher,
        protected ChannelEventBusInterface $eventBus,
    ) {
    }

    public function getChannelName(): string
    {
        return $this->channelName;
    }

    public function setDispatcher(DispatcherInterface $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }

    public function setIncomingChannel(IncomingChannelInterface $incomingChannel): void
    {
        $this->incomingChannel = $incomingChannel;
    }

    public function setOutgoingChannel(OutgoingChannelInterface $outgoingChannel): void
    {
        $this->outgoingChannel = $outgoingChannel;
    }

    /**
     * @param class-string<EventInterface> $event
     * @param callable $callback
     * @return void
     */
    public function on(string $event, callable $callback): void
    {
        $this->eventBus->listen($event, $callback);
    }

    public function handleProcessingExceptions(Closure $callback): void
    {
        $this->processingExceptionsHandlers[] = $callback;
    }

    protected function processingException(
        Throwable $exception,
        SessionInterface $session,
        MessageContextInterface $messageContext,
        ?IMessageInterface $message = null,
    ): void {
        if (count($this->processingExceptionsHandlers) === 0) {
            throw $exception;
        }

        foreach ($this->processingExceptionsHandlers as $handler) {
            $handler($exception, $session, $messageContext, $message);
        }
    }

    /**
     * @throws Exception
     */
    public function showPage(
        MessageContextInterface|string $context,
        string $pageClass,
        array $pageAttributes = []
    ): void {
        if (is_string($context)) {
            $context = MessageContext::createFromIds($context, $context);
        }

        $session = $this->sessionStore->load($context);

        $session->setPageState(
            PageState::create($pageClass, $pageAttributes)
        );

        $this->processPage(
            $session->getPageState(),
            $session,
            $context,
            null,
            fn(OMessageInterface $message, SessionInterface $session, ?PageInterface $contextPage) => $this
                ->handleOutgoingMessage($message, $session, $contextPage)
        );

        $session->save();
    }

    public function sendMessage(
        MessageContextInterface|string $context,
        OMessageInterface|string $message
    ): OMessageInterface {
        if (is_string($context)) {
            $context = MessageContext::createFromIds($context, $context);
        }

        if (is_string($message)) {
            $message = TextOutgoingMessage::make($message);
        }

        $message->setContext($context);

        $session = $this->sessionStore->load($context);

        $result = $this->handleOutgoingMessage($message, $session, null);

        $session->save();

        return $result;
    }

    public function getConfig(): SimpleConfigInterface
    {
        return $this->config;
    }

    /**
     * @param IMessageInterface $message
     * @param ?Closure(OMessageInterface, SessionInterface, PageInterface):OMessageInterface $outgoingCallback
     * @throws Exception
     */
    public function process(IMessageInterface $message, ?Closure $outgoingCallback = null): void
    {
        $session = $this->sessionStore->load($message->getContext());

        $pageState = $this->router->getCurrentPageState(
            $message,
            $session,
            $this->getDefaultEntryPoint()
        );

        $message = $this->incomingChannel->preprocess($message, $session, $pageState);

        try {
            $this->processPage($pageState, $session, $message->getContext(), $message, $outgoingCallback);
            $session->save();
        } catch (Throwable $exception) {
            $session->close();
            $this->processingException($exception, $session, $message->getContext(), $message);
        }
    }

    /**
     * @throws Exception
     */
    protected function processPage(
        PageStateInterface $pageState,
        SessionInterface $session,
        MessageContextInterface $messageContext,
        ?IMessageInterface $message = null,
        ?Closure $outgoingCallback = null
    ): void {
        if ($message) {
            $this->eventBus->fire(
                new IncomingMessageProcessingEvent($pageState, $message, $session)
            );
        }

        $this
            ->makePendingPage($pageState, $session, $messageContext, $message)
            ->dispatch(
                callback: function (
                    OMessageInterface $message,
                    ?PageInterface $contextPage
                ) use (
                    $session,
                    $outgoingCallback
                ) {
                    $this->eventBus->fire(
                        new OutgoingMessageEmittedEvent($message, $session, $contextPage)
                    );

                    return $outgoingCallback
                        ? ($outgoingCallback($message, $session, $contextPage) ?? $message)
                        : $message;
                }
            );
    }

    public function dispatch(IMessageInterface $message): void
    {
        $this->eventBus->fire(
            new IncomingMessageDispatchingEvent($message)
        );

        $this->dispatcher->dispatch(
            $this,
            $message,
            fn(OMessageInterface $message, SessionInterface $session, ?PageInterface $contextPage) => $this
                ->handleOutgoingMessage($message, $session, $contextPage)
        );
    }

    /**
     * @param DataFetcherInterface $dataFetcher
     * @return void
     */
    public function listen(DataFetcherInterface $dataFetcher): void
    {
        $this->incomingChannel->listen(
            $dataFetcher,
            fn(IMessageInterface $message) => $this->dispatch($message)
        );
    }

    protected function sendMessageViaOutgoingChannel(
        OMessageInterface $message,
        SessionInterface $session,
        ?PageInterface $contextPage,
    ): OMessageInterface {
        return $this->outgoingChannel->send($message, $session, $contextPage);
    }

    protected function handleOutgoingMessage(
        OMessageInterface $message,
        SessionInterface $session,
        ?PageInterface $contextPage,
    ): OMessageInterface {
        $this->eventBus->fire(
            new OutgoingMessageSendingEvent($message, $session, $contextPage)
        );

        $result = $this->sendMessageViaOutgoingChannel($message, $session, $contextPage);

        $this->eventBus->fire(
            new OutgoingMessageSentEvent($result, $session, $contextPage)
        );

        return $result;
    }

    protected function getDefaultEntryPoint(): string
    {
        return $this->config->get('entry');
    }

    protected function makePendingPage(
        PageStateInterface $pageState,
        SessionInterface $session,
        MessageContextInterface $messageContext,
        ?IMessageInterface $message = null,
    ): PendingDispatchPage {
        $pendingDispatchPage = new PendingDispatchPage(
            $this->channelName,
            $this->eventBus,
            $pageState,
            $session,
            $messageContext,
            $message,
        );

        $pendingDispatchPage->withBreadcrumbs();

        return $pendingDispatchPage;
    }
}

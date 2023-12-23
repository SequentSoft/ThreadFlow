<?php

declare(strict_types=1);

namespace SequentSoft\ThreadFlow;

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
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Service\BotStartedIncomingServiceMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface as OMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Router\RouterInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;
use SequentSoft\ThreadFlow\Events\Bot\SessionStartedEvent;
use SequentSoft\ThreadFlow\Events\Message\IncomingMessageDispatchingEvent;
use SequentSoft\ThreadFlow\Events\Message\IncomingMessageProcessingEvent;
use SequentSoft\ThreadFlow\Events\Message\OutgoingMessageSendingEvent;
use SequentSoft\ThreadFlow\Events\Message\OutgoingMessageSentEvent;
use SequentSoft\ThreadFlow\Messages\Outgoing\Regular\TextOutgoingMessage;
use SequentSoft\ThreadFlow\Session\PageState;
use SequentSoft\ThreadFlow\Session\Session;
use SequentSoft\ThreadFlow\Testing\ResultsRecorder;
use SequentSoft\ThreadFlow\Traits\HandleExceptions;
use SequentSoft\ThreadFlow\Traits\MakesPendingPages;
use Throwable;

class ChannelBot implements BotInterface
{
    use HandleExceptions;
    use MakesPendingPages;

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

    /**
     * @throws Exception
     */
    public function showPage(
        MessageContextInterface|string $context,
        string $pageClass,
        array $pageAttributes = []
    ): void {
        $context = $this->resolveContext($context);
        $session = $this->createOrGetSession($context);
        $pageState = PageState::create($pageClass, $pageAttributes);

        $session->setPageState($pageState);
        $this->processPage($pageState, $session, $context);
        $session->save();
    }

    protected function resolveContext(MessageContextInterface|string $context): MessageContextInterface
    {
        return is_string($context) ? MessageContext::createFromIds($context, $context) : $context;
    }

    public function sendMessage(
        MessageContextInterface|string $context,
        OMessageInterface|string $message,
        bool $useSession = false
    ): OMessageInterface {
        $context = $this->resolveContext($context);

        if (is_string($message)) {
            $message = TextOutgoingMessage::make($message);
        }

        $message->setContext($context);

        if ($useSession) {
            $session = $this->createOrGetSession($context);
            $result = $this->deliverMessage($message, $session);
            $session->save();
        } else {
            $result = $this->deliverMessage($message, new Session());
        }

        return $result;
    }

    public function getConfig(): SimpleConfigInterface
    {
        return $this->config;
    }

    /**
     * @param IMessageInterface $message
     * @throws Throwable
     */
    protected function processDispatchedIncomingMessage(IMessageInterface $message): void
    {
        $session = $this->createOrGetSession(
            context: $message->getContext(),
            fresh: $message instanceof BotStartedIncomingServiceMessageInterface
        );

        $pageState = $this->router->getCurrentPageState(
            $message,
            $session,
            $this->getDefaultEntryPoint()
        );

        $message = $this->incomingChannel->preprocess($message, $session, $pageState);

        try {
            $this->processPage($pageState, $session, $message->getContext(), $message);
            $session->save();
        } catch (Throwable $exception) {
            $session->close();
            $this->handleException($this->getChannelName(), $exception, $session, $message->getContext(), $message);
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
    ): void {
        if ($message) {
            $this->eventBus->fire(
                new IncomingMessageProcessingEvent($pageState, $message, $session)
            );
        }

        $this
            ->makePendingPage(
                channelName: $this->getChannelName(),
                eventBus: $this->eventBus,
                pageState: $pageState,
                session: $session,
                messageContext: $messageContext,
                message: $message
            )
            ->withBreadcrumbs()
            ->dispatch(
                callback: fn(OMessageInterface $message, ?PageInterface $contextPage) => $this->deliverMessage(
                    $message,
                    $session,
                    $contextPage
                ) ?? $message
            );
    }

    public function dispatch(IMessageInterface $message): void
    {
        $this->eventBus->fire(
            new IncomingMessageDispatchingEvent($message)
        );

        $this->dispatcher->dispatch(
            $this->getChannelName(),
            $message,
            fn($message) => $this->processDispatchedIncomingMessage($message)
        );
    }

    /**
     * @param DataFetcherInterface $dataFetcher
     * @return void
     */
    public function listen(DataFetcherInterface $dataFetcher): void
    {
        $this->incomingChannel->listen($dataFetcher, $this->dispatch(...));
    }

    protected function deliverMessage(
        OMessageInterface $message,
        SessionInterface $session,
        ?PageInterface $contextPage = null,
    ): OMessageInterface {
        $this->eventBus->fire(
            new OutgoingMessageSendingEvent($message, $session, $contextPage)
        );

        $result = $this->outgoingChannel->send($message, $session, $contextPage);

        $this->eventBus->fire(
            new OutgoingMessageSentEvent($result, $session, $contextPage)
        );

        return $result;
    }

    protected function createOrGetSession(MessageContextInterface $context, bool $fresh = false): SessionInterface
    {
        $session = $fresh
            ? $this->sessionStore->new($context)
            : $this->sessionStore->load($context);

        $this->eventBus->fire(new SessionStartedEvent($session));

        return $session;
    }

    protected function getDefaultEntryPoint(): string
    {
        return $this->config->get('entry');
    }

    public function testInput(
        IMessageInterface|string $message,
        ?MessageContextInterface $context = null
    ): ResultsRecorder {
        throw new Exception('This method is only for testing, please use FakeChannelBot instead');
    }

    public function withState(string $pageClass, array $attributes = []): static
    {
        throw new Exception('This method is only for testing, please use FakeChannelBot instead');
    }
}

<?php

namespace SequentSoft\ThreadFlow\Channel;

use DateTimeImmutable;
use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Contracts\Channel\ChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Service\BotStartedIncomingServiceMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\TextOutgoingRegularMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PendingDispatchPageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;
use SequentSoft\ThreadFlow\Events\Bot\SessionStartedEvent;
use SequentSoft\ThreadFlow\Events\Message\IncomingMessageDispatchingEvent;
use SequentSoft\ThreadFlow\Messages\Incoming\Regular\TextIncomingRegularMessage;
use SequentSoft\ThreadFlow\Messages\Outgoing\Regular\TextOutgoingMessage;
use SequentSoft\ThreadFlow\Page\PendingDispatchPage;
use SequentSoft\ThreadFlow\Session\PageState;
use SequentSoft\ThreadFlow\Testing\ResultsRecorder;
use SequentSoft\ThreadFlow\Traits\HandleExceptions;
use SequentSoft\ThreadFlow\Traits\TestInputResults;
use Throwable;

abstract class Channel implements ChannelInterface
{
    use HandleExceptions;
    use TestInputResults;

    public function __construct(
        protected string $channelName,
        protected ConfigInterface $config,
        protected SessionStoreInterface $sessionStore,
        protected DispatcherFactoryInterface $dispatcherFactory,
        protected EventBusInterface $eventBus,
    ) {
    }

    public function getConfig(): ConfigInterface
    {
        return $this->config;
    }

    protected function makeIncomingMessageFromText(
        string $id,
        string $text,
        DateTimeImmutable $date,
        MessageContextInterface $context
    ): ?IncomingMessageInterface {
        return new TextIncomingRegularMessage(
            $id,
            $context,
            $date,
            $text,
        );
    }

    public function incoming(IncomingMessageInterface $message): void
    {
        $this->sessionStore->useSession(
            $message->getContext(),
            function (SessionInterface $session) use ($message) {
                if ($message instanceof BotStartedIncomingServiceMessageInterface) {
                    $session->reset();
                }
                $this->eventBus->fire(new SessionStartedEvent($session));
                $this->dispatch($message, $session);
            }
        );
    }

    protected function dispatch(IncomingMessageInterface $message, SessionInterface $session): void
    {
        $this->eventBus->fire(
            new IncomingMessageDispatchingEvent($message)
        );

        try {
            $this->getDispatcher()->incoming($message, $session);
        } catch (Throwable $exception) {
            $this->handleException(
                $this->channelName,
                $exception,
                $session,
                $message->getContext(),
                $message,
            );
        }
    }

    protected function getDispatcher(): DispatcherInterface
    {
        return $this->dispatcherFactory->make(
            $this->config->get('dispatcher'),
            $this->channelName,
            $this->eventBus,
            $this->config,
            $this->outgoing(...)
        );
    }

    public function on(string $event, callable $callback): void
    {
        $this->eventBus->listen($event, $callback);
    }

    public function showPage(
        MessageContextInterface|string $context,
        PendingDispatchPageInterface|string $page,
        array $pageAttributes = []
    ): void {
        if (is_string($context)) {
            $context = MessageContext::createFromIds($context);
        }

        $page = is_string($page)
            ? new PendingDispatchPage($page, $pageAttributes)
            : $page->appendAttributes($pageAttributes);

        $this->sessionStore->useSession($context, function (SessionInterface $session) use ($page, $context) {
            $this->eventBus->fire(new SessionStartedEvent($session));
            $this->getDispatcher()->transition($context, $session, $page);
        });
    }

    public function sendMessage(
        MessageContextInterface|string $context,
        OutgoingMessageInterface|string $message,
    ): OutgoingMessageInterface {
        if (is_string($context)) {
            $context = MessageContext::createFromIds($context);
        }

        if (is_string($message)) {
            $message = $this->makeTextMessage($message, $context);
        }

        return $this->getDispatcher()->outgoing($message, null, null);
    }

    protected function makeTextMessage(
        string $text,
        MessageContextInterface $messageContext
    ): TextOutgoingRegularMessageInterface {
        return TextOutgoingMessage::make($text)
            ->setContext($messageContext);
    }

    protected function testInputText(string $text): IncomingMessageInterface
    {
        return new TextIncomingRegularMessage(
            'test',
            $this->fakeMessageContext(),
            new DateTimeImmutable(),
            $text,
        );
    }

    public function fakeMessageContext(): MessageContextInterface
    {
        return MessageContext::createFromIds('test-participant', 'test-chat');
    }

    public function testInput(
        string|IncomingMessageInterface $message,
        string|PageStateInterface|null $state = null,
        array $sessionAttributes = [],
    ): ResultsRecorder {
        $message = is_string($message)
            ? $this->testInputText($message)
            : $message;

        $state = is_string($state)
            ? PageState::create($state)
            : $state;

        return $this->captureTestInputResults(
            $this->eventBus,
            fn () => $this->sessionStore->useSession(
                $message->getContext(),
                function (SessionInterface $session) use ($message, $state, $sessionAttributes) {
                    if ($state) {
                        $session->setPageState($state);
                    }

                    foreach ($sessionAttributes as $key => $value) {
                        $session->set($key, $value);
                    }

                    $this->eventBus->fire(new SessionStartedEvent($session));
                    $this->dispatch($message, $session);
                }
            )
        );
    }

    abstract protected function outgoing(
        OutgoingMessageInterface $message,
        ?SessionInterface $session,
        ?PageInterface $contextPage
    ): OutgoingMessageInterface;
}

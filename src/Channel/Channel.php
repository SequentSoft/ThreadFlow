<?php

namespace SequentSoft\ThreadFlow\Channel;

use Closure;
use DateTimeImmutable;
use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Contracts\Channel\ChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\TextOutgoingRegularMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PendingDispatchPageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;
use SequentSoft\ThreadFlow\Events\Bot\SessionStartedEvent;
use SequentSoft\ThreadFlow\Events\Message\IncomingMessageDispatchingEvent;
use SequentSoft\ThreadFlow\Messages\Incoming\Regular\TextIncomingRegularMessage;
use SequentSoft\ThreadFlow\Messages\Outgoing\Regular\TextOutgoingMessage;
use SequentSoft\ThreadFlow\Page\PendingDispatchPage;

abstract class Channel implements ChannelInterface
{
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
        return (new TextIncomingRegularMessage(
            $id,
            $context,
            $date,
            $text,
        ));
    }

    public function incoming(IncomingMessageInterface $message): void
    {
        $this->session(
            $message->getContext(),
            function (SessionInterface $session) use ($message) {
                $this->dispatch($message, $session);
            }
        );
    }

    protected function dispatch(IncomingMessageInterface $message, SessionInterface $session): void
    {
        $this->eventBus->fire(
            new IncomingMessageDispatchingEvent($message)
        );

        $this->getDispatcher()->incoming($message, $session);
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
    //
    //    public function testInput(
    //    IMessageInterface|string $message,
    //    ?MessageContextInterface $context = null
    //): ResultsRecorder {
    //    throw new Exception('This method is only for testing, please use FakeChannelBot instead');
    //}
    //
    //public function withState(string $pageClass, array $attributes = []): static
    //{
    //    throw new Exception('This method is only for testing, please use FakeChannelBot instead');
    //}


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

        $this->session($context, function (SessionInterface $session) use ($page, $pageAttributes, $context) {
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

        return $this->outgoing($message, null, null);
    }

    protected function makeTextMessage(
        string $text,
        MessageContextInterface $messageContext
    ): TextOutgoingRegularMessageInterface {
        return TextOutgoingMessage::make($text)
            ->setContext($messageContext);
    }

    abstract protected function outgoing(
        OutgoingMessageInterface $message,
        ?SessionInterface $session,
        ?PageInterface $contextPage
    ): OutgoingMessageInterface;

    protected function session(MessageContextInterface $context, Closure $callback): void
    {
        $session = $this->sessionStore->load($context);

        $this->eventBus->fire(new SessionStartedEvent($session));

        $callback($session);

        $this->sessionStore->save($context, $session);
    }
}

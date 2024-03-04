<?php

namespace SequentSoft\ThreadFlow\Channel;

use Closure;
use DateTimeImmutable;
use SequentSoft\ThreadFlow\Builders\ChannelPendingSend;
use SequentSoft\ThreadFlow\Contracts\Channel\ChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\ParticipantInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\RoomInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\CommonIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Service\BotStartedIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\CommonOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\TextOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;
use SequentSoft\ThreadFlow\Contracts\Testing\ResultsRecorderInterface;
use SequentSoft\ThreadFlow\Events\Bot\SessionStartedEvent;
use SequentSoft\ThreadFlow\Events\Message\IncomingMessageDispatchingEvent;
use SequentSoft\ThreadFlow\Messages\Incoming\Regular\TextIncomingMessage;
use SequentSoft\ThreadFlow\Messages\Outgoing\Regular\TextOutgoingMessage;
use SequentSoft\ThreadFlow\Testing\PendingTestInput;
use SequentSoft\ThreadFlow\Traits\HandleExceptions;
use SequentSoft\ThreadFlow\Traits\HasUserResolver;
use SequentSoft\ThreadFlow\Traits\TestInputResults;
use Throwable;

abstract class Channel implements ChannelInterface
{
    use HandleExceptions;
    use TestInputResults;
    use HasUserResolver;

    public function __construct(
        protected string $channelName,
        protected ConfigInterface $config,
        protected SessionStoreInterface $sessionStore,
        protected DispatcherFactoryInterface $dispatcherFactory,
        protected EventBusInterface $eventBus,
    ) {
    }

    public function getName(): string
    {
        return $this->channelName;
    }

    public function getConfig(): ConfigInterface
    {
        return $this->config;
    }

    /**
     * This method is used for cli channels to make incoming message from input text.
     * Can be overridden in specific channel implementation.
     */
    protected function makeIncomingMessageFromText(
        string $id,
        string $text,
        DateTimeImmutable $date,
        MessageContextInterface $context
    ): ?CommonIncomingMessageInterface {
        return new TextIncomingMessage($id, $context, $date, $text);
    }

    private function useSession(
        MessageContextInterface $context,
        Closure $callback
    ): mixed {
        return $this->sessionStore->useSession($context, function (SessionInterface $session) use ($callback) {
            $this->eventBus->fire(new SessionStartedEvent($session));
            return call_user_func($callback, $session);
        });
    }

    /**
     * This method is called when a new message is received from the channel.
     * It is the main entry point for the channel to handle incoming messages.
     */
    public function incoming(CommonIncomingMessageInterface $message): void
    {
        $this->useSession(
            $message->getContext(),
            fn (SessionInterface $session) => $this->dispatch($message, $session)
        );
    }

    protected function dispatch(CommonIncomingMessageInterface $message, SessionInterface $session): void
    {
        // Reset the session if the bot has been started or restarted.
        // This is useful for channels like Telegram where the bot can be restarted.
        if ($message instanceof BotStartedIncomingMessageInterface) {
            $session->reset();
        }

        if ($this->userResolver) {
            $session->setUserResolver(
                fn (SessionInterface $session) => call_user_func($this->userResolver, $session, $message->getContext())
            );
        }

        $this->eventBus->fire(
            new IncomingMessageDispatchingEvent($message)
        );

        try {
            $this->getDispatcher()->incoming($message, $session);
        } catch (Throwable $exception) {
            $this->handleException($exception, $session, $message->getContext(), $message);
        }
    }

    protected function getDispatcher(): DispatcherInterface
    {
        return $this->dispatcherFactory->make(
            $this->config->get('dispatcher'),
            $this->config->get('entry'),
            $this->eventBus,
            $this->outgoing(...)
        );
    }

    public function on(string $event, callable $callback): void
    {
        $this->eventBus->listen($event, $callback);
    }

    public function forParticipant(string|ParticipantInterface $participant): ChannelPendingSend
    {
        return (new ChannelPendingSend($this, $this->makeTextMessage(...)))
            ->withParticipant($participant);
    }

    public function forRoom(string|RoomInterface $room): ChannelPendingSend
    {
        return (new ChannelPendingSend($this, $this->makeTextMessage(...)))
            ->withRoom($room);
    }

    public function dispatchTo(
        MessageContextInterface $context,
        PageInterface|CommonOutgoingMessageInterface $pageOrMessage,
    ): ?CommonOutgoingMessageInterface {
        $pageOrMessage->setContext($context);

        return $this->useSession(
            $context,
            fn (SessionInterface $session) => $this->processDispatchTo(
                $context,
                $session->getCurrentPage(),
                $session,
                $pageOrMessage
            )
        );
    }

    protected function processDispatchTo(
        MessageContextInterface $messageContext,
        PageInterface $contextPage,
        SessionInterface $session,
        PageInterface|CommonOutgoingMessageInterface $pageOrMessage,
    ): ?CommonOutgoingMessageInterface {
        if ($session->getCurrentPage()->isDontDisturb()) {
            $session->pushPendingInteraction($pageOrMessage);
            return null;
        }

        if ($this->userResolver) {
            $session->setUserResolver(
                fn (SessionInterface $session) => call_user_func($this->userResolver, $session, $messageContext)
            );
        }

        if ($pageOrMessage instanceof PageInterface) {
            $this->getDispatcher()->transition($messageContext, $session, $pageOrMessage, $contextPage);
            return null;
        }

        return $this->getDispatcher()->outgoing($pageOrMessage, $session, null);
    }

    protected function makeTextMessage(
        string $text,
        MessageContextInterface $messageContext
    ): TextOutgoingMessageInterface {
        return TextOutgoingMessage::make($text)
            ->setContext($messageContext);
    }

    /**
     * This method is used for testing purposes to create an incoming message from text.
     * Can be overridden in specific channel implementation.
     */
    protected function testInputText(string $text, MessageContextInterface $context): CommonIncomingMessageInterface
    {
        return new TextIncomingMessage(
            'test',
            $context,
            new DateTimeImmutable(),
            $text,
        );
    }

    protected function pendingTestInputCallback(
        Closure $prepareSession,
        CommonIncomingMessageInterface $message
    ): ResultsRecorderInterface {
        return $this->captureTestInputResults(
            $this->eventBus,
            fn () => $this->useSession(
                $message->getContext(),
                fn (SessionInterface $session) => $this->dispatch($message, $prepareSession($session))
            )
        );
    }

    public function test(): PendingTestInput
    {
        $pendingTestInput = new PendingTestInput(
            $this->channelName,
            $this->pendingTestInputCallback(...)
        );

        $pendingTestInput->setTextMessageResolver(function (string $text, MessageContextInterface $context) {
            return $this->testInputText($text, $context);
        });

        return $pendingTestInput;
    }

    abstract protected function outgoing(
        CommonOutgoingMessageInterface $message,
        ?SessionInterface $session,
        ?PageInterface $contextPage
    ): CommonOutgoingMessageInterface;
}

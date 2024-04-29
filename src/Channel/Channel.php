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
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\SimpleKeyboardInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\BasicIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\ClickIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Service\BotStartedIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\BasicOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\TextOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;
use SequentSoft\ThreadFlow\Contracts\Testing\ResultsRecorderInterface;
use SequentSoft\ThreadFlow\Events\Bot\SessionClosedEvent;
use SequentSoft\ThreadFlow\Events\Bot\SessionStartedEvent;
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
    use HasUserResolver;
    use TestInputResults;

    protected ?SessionInterface $activeSession = null;

    public function __construct(
        protected string $channelName,
        protected ConfigInterface $config,
        protected SessionStoreInterface $sessionStore,
        protected DispatcherInterface $dispatcher,
        protected EventBusInterface $eventBus,
    ) {
        $this->dispatcher->setOutgoingCallback(
            $this->outgoing(...)
        );
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
    ): ?BasicIncomingMessageInterface {
        return new TextIncomingMessage($id, $context, $date, $text);
    }

    private function useSession(
        MessageContextInterface $context,
        Closure $callback
    ): mixed {
        if ($this->activeSession) {
            return call_user_func($callback, $this->activeSession);
        }

        return $this->sessionStore->useSession($context, function (SessionInterface $session) use ($callback) {
            $this->activeSession = $session;
            $this->eventBus->fire(new SessionStartedEvent($session));
            $result = call_user_func($callback, $session);
            $this->eventBus->fire(new SessionClosedEvent($session));
            $this->activeSession = null;

            return $result;
        });
    }

    /**
     * This method is called when a new message is received from the channel.
     * It is the main entry point for the channel to handle incoming messages.
     */
    public function incoming(BasicIncomingMessageInterface $message): void
    {
        try {
            $this->useSession(
                $message->getContext(),
                fn (SessionInterface $session) => $this->dispatch($message, $session)
            );
        } catch (Throwable $exception) {
            $this->handleException($exception, $message->getContext());
        }
    }

    protected function dispatch(BasicIncomingMessageInterface $message, SessionInterface $session): void
    {
        // Reset the session if the bot has been started or restarted.
        // This is useful for channels like Telegram where the bot can be restarted.
        if ($message instanceof BotStartedIncomingMessageInterface) {
            $session->reset();
        }

        if ($this->userResolver) {
            $message->getContext()->setUserResolver($this->userResolver);
        }

        $page = $this->getIncomingMessagePage($session);

        $message = $this->prepareIncomingMessage($message, $session, $page);

        $this->dispatcher->incoming($message, $session, $page);
    }

    protected function prepareIncomingKeyboardClick(
        BasicIncomingMessageInterface $message,
        SessionInterface $session,
        SimpleKeyboardInterface $keyboard
    ): ?ClickIncomingMessageInterface {
        return null;
    }

    protected function prepareIncomingMessage(
        BasicIncomingMessageInterface $message,
        SessionInterface $session,
        PageInterface $page
    ): BasicIncomingMessageInterface {
        $keyboard = $page->getLastKeyboard();

        if ($keyboard && $click = $this->prepareIncomingKeyboardClick($message, $session, $keyboard)) {
            return $click;
        }

        return $message;
    }

    protected function getIncomingMessagePage(SessionInterface $session): PageInterface
    {
        if ($page = $session->getCurrentPage()) {
            return $page;
        }

        $pageClass = $this->getEntryPage();

        if ($pageClass instanceof PageInterface) {
            return $pageClass;
        }

        return new $pageClass();
    }

    protected function getEntryPage(): string|PageInterface
    {
        return $this->config->get('entry');
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
        PageInterface|BasicOutgoingMessageInterface $pageOrMessage,
        bool $force = false,
    ): ?BasicOutgoingMessageInterface {
        $pageOrMessage->setContext($context);

        return $this->useSession(
            $context,
            fn (SessionInterface $session) => $this->processDispatchTo(
                $context,
                $session->getCurrentPage(),
                $session,
                $pageOrMessage,
                $force
            )
        );
    }

    protected function processDispatchTo(
        MessageContextInterface $messageContext,
        PageInterface $contextPage,
        SessionInterface $session,
        PageInterface|BasicOutgoingMessageInterface $pageOrMessage,
        bool $force = false,
    ): ?BasicOutgoingMessageInterface {
        if (! $force && $session->getCurrentPage()->isDontDisturb()) {
            $this->dispatcher->pushPendingMessage($messageContext, $session, $pageOrMessage);

            return null;
        }

        if ($this->userResolver) {
            $messageContext->setUserResolver($this->userResolver);
        }

        if ($pageOrMessage instanceof PageInterface) {
            $this->dispatcher->transition($messageContext, $session, $pageOrMessage, $contextPage);

            return null;
        }

        return $this->dispatcher->outgoing($pageOrMessage, $session, null);
    }

    protected function makeTextMessage(
        string $text,
        MessageContextInterface $messageContext
    ): TextOutgoingMessageInterface {
        return TextOutgoingMessage::make($text)
            ->setContext($messageContext);
    }

    protected function pendingTestInputCallback(
        Closure $prepareSession,
        BasicIncomingMessageInterface $message
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
        return new PendingTestInput(
            $this->channelName,
            $this->pendingTestInputCallback(...)
        );
    }

    abstract protected function outgoing(
        BasicOutgoingMessageInterface $message,
        ?SessionInterface $session,
        ?PageInterface $contextPage
    ): BasicOutgoingMessageInterface;
}

<?php

namespace SequentSoft\ThreadFlow\Page;

use Closure;
use ReflectionClass;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Events\ChannelEventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\IncomingRegularMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Service\IncomingServiceMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\OutgoingRegularMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PendingDispatchInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PendingDispatchPageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionDataInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Enums\Messages\TypingType;
use SequentSoft\ThreadFlow\Events\Page\PageHandleRegularMessageEvent;
use SequentSoft\ThreadFlow\Events\Page\PageHandleServiceMessageEvent;
use SequentSoft\ThreadFlow\Events\Page\PageShowEvent;
use SequentSoft\ThreadFlow\Messages\Incoming\Service\BotStartedIncomingServiceMessage;
use SequentSoft\ThreadFlow\Messages\Outgoing\OutgoingMessage;
use SequentSoft\ThreadFlow\Messages\Outgoing\Service\TypingOutgoingServiceMessage;
use SequentSoft\ThreadFlow\Session\PageState;
use Throwable;

abstract class AbstractPage implements PageInterface
{
    private Closure $outgoingCallback;

    public function __construct(
        private readonly string $channelName,
        private readonly ChannelEventBusInterface $eventBus,
        private readonly PageStateInterface $state,
        private readonly SessionInterface $session,
        private readonly MessageContextInterface $messageContext,
        private readonly ?IncomingMessageInterface $message,
    ) {
    }

    public function isBackground(): bool
    {
        return $this->state !== $this->session->getPageState();
    }

    public function getState(): PageStateInterface
    {
        return $this->state;
    }

    public function execute(Closure $callback): ?PendingDispatchInterface
    {
        $this->outgoingCallback = $callback;

        $this->populateAttributes();

        $result = $this->handleIncoming();

        $this->storeAttributes();

        return $result;
    }

    private function populateAttributes(): void
    {
        $attributes = $this->state->getAttributes();

        (function (array $attributes) {
            foreach ($attributes as $key => $value) {
                try {
                    $this->{$key} = $value;
                } catch (Throwable $throwable) {
                    $this->populateAttributeErrorHandler($throwable, $key, $value);
                }
            }
        })->call($this, $attributes);
    }

    protected function populateAttributeErrorHandler(Throwable $throwable, string $key, mixed $value)
    {
        $classReflection = new ReflectionClass(static::class);
        $propertyReflection = $classReflection->getProperty($key);
        $defaultValue = $propertyReflection->getDefaultValue();

        $this->{$key} = $defaultValue;
    }

    private function handleIncoming(): ?PendingDispatchInterface
    {
        if ($this->message instanceof IncomingRegularMessageInterface) {
            $this->eventBus->fire(new PageHandleRegularMessageEvent($this, $this->message));
            return $this->executeRegularMessageHandler($this->message);
        }

        if ($this->message instanceof IncomingServiceMessageInterface) {
            $this->eventBus->fire(new PageHandleServiceMessageEvent($this, $this->message));
            return $this->executeServiceMessageHandler($this->message);
        }

        $this->eventBus->fire(new PageShowEvent($this));
        return $this->executeShowHandler();
    }

    private function executeShowHandler(): ?PendingDispatchInterface
    {
        if (method_exists($this, 'show')) {
            return $this->show();
        }

        return null;
    }

    private function executeRegularMessageHandler(
        IncomingRegularMessageInterface $message
    ): ?PendingDispatchInterface {
        if (method_exists($this, 'handleMessage')) {
            return $this->handleMessage($message);
        }

        return null;
    }

    private function executeServiceMessageHandler(
        IncomingServiceMessageInterface $message
    ): ?PendingDispatchInterface {
        if ($message instanceof BotStartedIncomingServiceMessage && method_exists($this, 'welcome')) {
            return $this->welcome($message);
        }

        if (method_exists($this, 'handleServiceMessage')) {
            return $this->handleServiceMessage($message);
        }

        return null;
    }

    protected function showTyping(TypingType $type = TypingType::TYPING): void
    {
        $this->reply(
            TypingOutgoingServiceMessage::make($type)
        );
    }

    private function storeAttributes(): void
    {
        $attributes = (function () {
            return get_object_vars($this);
        })->call($this);

        $this->state->setAttributes($attributes);
    }

    protected function session(): SessionDataInterface
    {
        return $this->session->getData();
    }

    protected function back(
        ?string $fallbackPageClass = null,
        array $fallbackPageAttributes = []
    ): ?PendingDispatchPageInterface {
        $prevState = $this->session->getBreadcrumbs()->pop();

        if ($prevState) {
            return (new PendingDispatchPage(
                $this->channelName,
                $this->eventBus,
                $prevState,
                $this->session,
                $this->messageContext,
                null
            ))->withBreadcrumbsReplace();
        }

        if ($fallbackPageClass) {
            return $this->next($fallbackPageClass, $fallbackPageAttributes);
        }

        return null;
    }

    protected function next(string $pageClass, array $attributes = []): PendingDispatchPageInterface
    {
        return new PendingDispatchPage(
            $this->channelName,
            $this->eventBus,
            PageState::create($pageClass, $attributes),
            $this->session,
            $this->messageContext,
            null
        );
    }

    /**
     * @phpstan-template T of OutgoingMessage
     * @phpstan-param T $message
     * @phpstan-return T
     */
    protected function reply(OutgoingMessage $message): OutgoingMessage
    {
        $message->setId(null);

        if (! $message->getContext()) {
            $message->setContext($this->messageContext);
        }

        return call_user_func($this->outgoingCallback, $message, $this);
    }

    /**
     * @phpstan-template T of OutgoingMessage
     * @phpstan-param T $message
     * @phpstan-return T
     */
    protected function updateMessage(OutgoingMessage $message): OutgoingMessage
    {
        if (! $message->getId()) {
            throw new \InvalidArgumentException('Message id is required for update');
        }

        if (! $message->getContext()) {
            $message->setContext($this->messageContext);
        }

        return call_user_func($this->outgoingCallback, $message, $this);
    }
}

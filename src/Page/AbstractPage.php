<?php

namespace SequentSoft\ThreadFlow\Page;

use Closure;
use ReflectionMethod;
use ReflectionUnionType;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Forms\FormInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\Buttons\BackButtonInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\CommonIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\ClickIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Service\IncomingServiceMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\CommonOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\HtmlOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\TextOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\DontDisturbPageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionDataInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Enums\Messages\TypingType;
use SequentSoft\ThreadFlow\Events\Page\PageHandleRegularMessageEvent;
use SequentSoft\ThreadFlow\Events\Page\PageHandleServiceMessageEvent;
use SequentSoft\ThreadFlow\Events\Page\PageHandleWelcomeMessageEvent;
use SequentSoft\ThreadFlow\Events\Page\PageShowEvent;
use SequentSoft\ThreadFlow\Messages\Incoming\Service\BotStartedIncomingMessage;
use SequentSoft\ThreadFlow\Messages\Outgoing\Regular\HtmlOutgoingMessage;
use SequentSoft\ThreadFlow\Messages\Outgoing\Regular\TextOutgoingMessage;
use SequentSoft\ThreadFlow\Messages\Outgoing\Service\TypingOutgoingServiceMessage;

abstract class AbstractPage implements PageInterface
{
    private const METHOD_SHOW = 'show';

    private const METHOD_ANSWER = 'answer';

    private const METHOD_SERVICE = 'service';

    private Closure $outgoingCallback;

    private SessionInterface $session;

    private MessageContextInterface $messageContext;

    /**
     * The unique identifier of the page
     */
    protected ?string $id = null;

    protected bool $trackingPrev = false;

    /**
     * The reference to the previous page if it is necessary to store it
     */
    protected ?PageInterface $prev = null;

    /**
     * This method is used to determine whether to store a reference
     * to the previous page after moving to the next page
     * can be overridden in the child class
     * (by default, the reference is stored if the previous page has a back button)
     */
    public function isTrackingPrev(): bool
    {
        return $this->trackingPrev;
    }

    /**
     * This method is used to set a reference to the previous page
     */
    public function setPrev(?PageInterface $prev): static
    {
        $this->prev = $prev;

        return $this;
    }

    /**
     * It can be used to get an instance of the previous page
     */
    public function getPrev(): ?PageInterface
    {
        return $this->prev;
    }

    /**
     * This method is used to get the unique identifier of the page
     * (if the identifier is not set, it will be generated automatically)
     */
    public function getId(): string
    {
        if ($this->id === null) {
            $this->id = md5(uniqid('', true));
        }

        return $this->id;
    }

    protected function getUser(): mixed
    {
        return $this->session->getUser();
    }

    public function setSession(SessionInterface $session): static
    {
        $this->session = $session;

        return $this;
    }

    public function setContext(MessageContextInterface $messageContext): static
    {
        $this->messageContext = $messageContext;

        return $this;
    }

    public function isBackground(): bool
    {
        return $this->getId() !== $this->session->getCurrentPage()?->getId();
    }

    public function getChannelName(): string
    {
        return $this->messageContext->getChannelName();
    }

    /**
     * This method is used to determine whether the page is in the "Do not disturb" mode
     * (i.e. the page does not accept any incoming messages not related to the page)
     */
    public function isDontDisturb(): bool
    {
        return $this instanceof DontDisturbPageInterface;
    }

    public function getContext(): MessageContextInterface
    {
        return $this->messageContext;
    }

    protected function buttons(): ?array
    {
        return null;
    }

    protected function textMessage(string $text): TextOutgoingMessageInterface
    {
        $message = TextOutgoingMessage::make($text);
        $message->withKeyboard($this->buttons());

        return $message;
    }

    protected function htmlMessage(string $text): HtmlOutgoingMessageInterface
    {
        $message = HtmlOutgoingMessage::make($text);
        $message->withKeyboard($this->buttons());

        return $message;
    }

    /**
     * This method is called when a message is received
     * and is used to handle the message
     */
    public function execute(
        EventBusInterface $eventBus,
        ?CommonIncomingMessageInterface $message,
        Closure $callback
    ): mixed {
        $this->outgoingCallback = $callback;

        if ($message && $message->getContext() === null) {
            $message->setContext($this->messageContext);
        }

        return $this->handleResult(
            $this->executeHandler($eventBus, $message)
        );
    }

    protected function handleResult(mixed $result): mixed
    {
        if (is_array($result)) {
            $result = json_encode(
                $result,
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
            );
        }

        if (is_numeric($result)) {
            $result = (string) $result;
        }

        if (is_string($result)) {
            $result = $this->textMessage($result);
        }

        if ($result instanceof CommonOutgoingMessageInterface && $result->getId() === null) {
            $this->reply($result);
            return null;
        }

        if ($result instanceof FormInterface) {
            return $this->makeFormPage($result);
        }

        return $result;
    }

    private function executeHandler(EventBusInterface $eventBus, ?CommonIncomingMessageInterface $message): mixed
    {
        if ($message instanceof IncomingMessageInterface) {
            return $this->executeRegularMessageHandler($message, $eventBus);
        }

        if ($message instanceof IncomingServiceMessageInterface) {
            return $this->executeServiceMessageHandler($message, $eventBus);
        }

        return $this->executeShowHandler($eventBus);
    }

    protected function makeFormPage(FormInterface $form): BaseFormPage
    {
        return new BaseFormPage($form, $this);
    }

    private function executeShowHandler(EventBusInterface $eventBus): mixed
    {
        if (! method_exists($this, self::METHOD_SHOW)) {
            return null;
        }

        $eventBus->fire(new PageShowEvent($this));

        $result = $this->callHandlerMethod(self::METHOD_SHOW, null);

        if ($result === $this) {
            throw new \RuntimeException('The infinite loop is detected. The page returns itself in the show method.');
        }

        return $result;
    }

    private function executeRegularMessageHandler(IncomingMessageInterface $message, EventBusInterface $eventBus): mixed
    {
        if ($message instanceof ClickIncomingMessageInterface) {
            $button = $message->getButton();

            if ($button instanceof BackButtonInterface && $button->isAutoHandleAnswer()) {
                return $this->prev;
            }
        }

        if (! method_exists($this, self::METHOD_ANSWER)) {
            return null;
        }

        $eventBus->fire(new PageHandleRegularMessageEvent($this, $message));

        if ($this->isArgumentAcceptableToAnswer($message)) {
            return $this->callHandlerMethod(self::METHOD_ANSWER, $message);
        }

        return $this->callHandlerMethod('invalidAnswer', $message);
    }

    private function executeServiceMessageHandler(
        IncomingServiceMessageInterface $message,
        EventBusInterface $eventBus
    ): mixed {
        if ($message instanceof BotStartedIncomingMessage) {
            if (method_exists($this, 'welcome')) {
                $eventBus->fire(new PageHandleWelcomeMessageEvent($this, $message));

                return $this->callHandlerMethod('welcome', $message);
            }

            // fallback to show
            return $this->executeShowHandler($eventBus);
        }

        if (method_exists($this, self::METHOD_SERVICE)) {
            return null;
        }

        $eventBus->fire(new PageHandleServiceMessageEvent($this, $message));

        return $this->callHandlerMethod(self::METHOD_SERVICE, $message);
    }

    /**
     * This method is used to determine whether the argument is acceptable to the answer method
     * (i.e. the argument is an instance of the type specified in the answer method)
     */
    private function isArgumentAcceptableToAnswer(mixed $argument): bool
    {
        $reflection = new ReflectionMethod($this, 'answer');

        $params = $reflection->getParameters();

        if (count($params) < 1) {
            return false;
        }

        $type = $params[0]->getType();

        if ($type === null) {
            return true;
        }

        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $type) {
                if ($argument instanceof ($type->getName())) {
                    return true;
                }
            }

            return false;
        }

        return $argument instanceof ($type->getName());
    }

    public function invalidAnswer(CommonIncomingMessageInterface $message)
    {
        if (method_exists($this, self::METHOD_SHOW)) {
            return $this->callHandlerMethod(self::METHOD_SHOW, null);
        }

        return null;
    }

    protected function callHandlerMethod(string $method, ?CommonIncomingMessageInterface $message): mixed
    {
        return $this->{$method}($message);
    }

    protected function showTyping(TypingType $type = TypingType::TYPING): void
    {
        $this->reply(
            TypingOutgoingServiceMessage::make($type)
        );
    }

    protected function sessionData(): SessionDataInterface
    {
        return $this->session->getData();
    }

    protected function prepareValueForSerialization(string $name, mixed $value): mixed
    {
        return $value;
    }

    protected function prepareValueAfterUnserialization(string $name, mixed $value): mixed
    {
        return $value;
    }

    protected function getSerializableAttributes(): array
    {
        return (function () {
            return get_object_vars($this);
        })->call($this);
    }

    public function __serialize(): array
    {
        $attributes = [];

        foreach ($this->getSerializableAttributes() as $name => $value) {
            $attributes[$name] = $this->prepareValueForSerialization($name, $value);
        }

        return $attributes;
    }

    public function __unserialize(array $data): void
    {
        (function (array $attributes) {
            foreach ($attributes as $key => $value) {
                $this->{$key} = $this->prepareValueAfterUnserialization($key, $value);
            }
        })->call($this, $data);
    }

    protected function handleOutgoingMessage(CommonOutgoingMessageInterface $message): CommonOutgoingMessageInterface
    {
        if ($message instanceof OutgoingMessageInterface) {
            foreach ($message->getKeyboard()?->getRows() ?? [] as $row) {
                foreach ($row->getButtons() as $button) {
                    if ($button instanceof BackButtonInterface) {
                        $this->trackingPrev = true;
                    }
                }
            }
        }

        if (! $message->getContext()) {
            $message->setContext($this->messageContext);
        }

        return call_user_func($this->outgoingCallback, $message);
    }

    /**
     * @phpstan-template T of CommonOutgoingMessageInterface
     * @phpstan-param T $message
     * @phpstan-return T
     */
    protected function reply(CommonOutgoingMessageInterface $message): CommonOutgoingMessageInterface
    {
        $message->setId(null);

        return $this->handleOutgoingMessage($message);
    }

    /**
     * @phpstan-template T of CommonOutgoingMessageInterface
     * @phpstan-param T $message
     * @phpstan-return T
     */
    protected function updateMessage(CommonOutgoingMessageInterface $message): CommonOutgoingMessageInterface
    {
        if (! $message->getId()) {
            throw new \InvalidArgumentException('Message id is required for update');
        }

        return $this->handleOutgoingMessage($message);
    }
}

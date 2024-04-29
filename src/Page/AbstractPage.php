<?php

namespace SequentSoft\ThreadFlow\Page;

use Closure;
use InvalidArgumentException;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Forms\FormInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\Buttons\BackButtonInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\Buttons\TextButtonInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\SimpleKeyboardInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\BasicIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\ClickIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Service\IncomingServiceMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\BasicOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\HtmlOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\TextOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\ActivePagesRepositoryInterface;
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
use SequentSoft\ThreadFlow\Traits\AcceptableArguments;
use SequentSoft\ThreadFlow\Traits\GenerateUniqueIdsTrait;

abstract class AbstractPage implements PageInterface
{
    use AcceptableArguments;
    use GenerateUniqueIdsTrait;

    private const METHOD_SHOW = 'show';

    private const METHOD_ANSWER = 'answer';

    private const METHOD_SERVICE = 'service';

    private Closure $outgoingCallback;

    private SessionInterface $session;

    private ActivePagesRepositoryInterface $activePagesRepository;

    private MessageContextInterface $messageContext;

    /**
     * The unique identifier of the page
     */
    protected ?string $id = null;

    protected bool $keepPrevPageReferenceAfterTransition = false;

    protected ?SimpleKeyboardInterface $lastKeyboard = null;

    /**
     * The reference to the previous page if it is necessary to store it
     */
    protected ?string $prevPageId = null;

    public function getLastKeyboard(): ?SimpleKeyboardInterface
    {
        return $this->lastKeyboard;
    }

    public function keepPrevPageReferenceAfterTransition(): bool
    {
        return $this->keepPrevPageReferenceAfterTransition;
    }

    protected function hasGetPrevPageMethod(): bool
    {
        return method_exists($this, 'getPrevPage');
    }

    public function autoSetPrevPageReference(): bool
    {
        return ! $this->hasGetPrevPageMethod();
    }

    public function setPrevPageId(?string $prevId): static
    {
        $this->prevPageId = $prevId;

        return $this;
    }

    public function getPrevPageId(): ?string
    {
        return $this->prevPageId;
    }

    public function resolvePrevPage(): ?PageInterface
    {
        if ($this->hasGetPrevPageMethod()) {
            return call_user_func([$this, 'getPrevPage']);
        }

        if (! $id = $this->getPrevPageId()) {
            return null;
        }

        return $this->activePagesRepository->get($this->getContext(), $this->session, $id);
    }

    /**
     * This method is used to get the unique identifier of the page
     * (if the identifier is not set, it will be generated automatically)
     */
    public function getId(): string
    {
        if ($this->id === null) {
            $this->id = static::generateUniqueId();
        }

        return $this->id;
    }

    protected function getUser(): mixed
    {
        return $this->messageContext->getUser();
    }

    public function setActivePagesRepository(ActivePagesRepositoryInterface $activePagesRepository): static
    {
        $this->activePagesRepository = $activePagesRepository;

        return $this;
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
        ?BasicIncomingMessageInterface $message,
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
        if ($result instanceof IncomingMessageInterface) {
            $result = $result->getText();
        }

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

        if ($result instanceof BasicOutgoingMessageInterface && $result->getId() === null) {
            $this->reply($result);

            return null;
        }

        if ($result instanceof FormInterface) {
            return $this->makeFormPage($result);
        }

        return $result;
    }

    protected function isShowMethodDefined(): bool
    {
        return method_exists($this, self::METHOD_SHOW);
    }

    protected function isWelcomeMethodDefined(): bool
    {
        return method_exists($this, 'welcome');
    }

    protected function isAnswerMethodDefined(): bool
    {
        return method_exists($this, self::METHOD_ANSWER);
    }

    protected function isServiceMethodDefined(): bool
    {
        return method_exists($this, self::METHOD_SERVICE);
    }

    private function executeHandler(EventBusInterface $eventBus, ?BasicIncomingMessageInterface $message): mixed
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
        if (! $this->isShowMethodDefined()) {
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
                return $this->getPrevPage();
            }

            if ($button instanceof TextButtonInterface && $page = $button->getAutoHandleAnswerPage()) {
                return $page;
            }
        }

        if (! $this->isAnswerMethodDefined()) {
            return $this->callHandlerMethod(self::METHOD_SHOW, $message);
        }

        $eventBus->fire(new PageHandleRegularMessageEvent($this, $message));

        if ($this->isArgumentAcceptableTo($this, self::METHOD_ANSWER, $message)) {
            return $this->callHandlerMethod(self::METHOD_ANSWER, $message);
        }

        return $this->callHandlerMethod('invalidAnswer', $message);
    }

    private function executeServiceMessageHandler(
        IncomingServiceMessageInterface $message,
        EventBusInterface $eventBus
    ): mixed {
        if ($message instanceof BotStartedIncomingMessage) {
            if ($this->isWelcomeMethodDefined()) {
                $eventBus->fire(new PageHandleWelcomeMessageEvent($this, $message));

                return $this->callHandlerMethod('welcome', $message);
            }

            // fallback to show
            return $this->executeShowHandler($eventBus);
        }

        if (! $this->isServiceMethodDefined()) {
            return null;
        }

        $eventBus->fire(new PageHandleServiceMessageEvent($this, $message));

        return $this->callHandlerMethod(self::METHOD_SERVICE, $message);
    }

    public function invalidAnswer(BasicIncomingMessageInterface $message): mixed
    {
        if ($this->isShowMethodDefined()) {
            return $this->callHandlerMethod(self::METHOD_SHOW, null);
        }

        return null;
    }

    protected function callHandlerMethod(string $method, ?BasicIncomingMessageInterface $message): mixed
    {
        return $this->{$method}($message);
    }

    protected function showTyping(TypingType $type = TypingType::TYPING): void
    {
        $this->reply(
            TypingOutgoingServiceMessage::make($type)
        );
    }

    public function getSessionId(): string
    {
        return $this->session->getId();
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

    public function getAttributes(): array
    {
        $attributes = [];

        foreach ($this->getSerializableAttributes() as $name => $value) {
            $attributes[$name] = $this->prepareValueForSerialization($name, $value);
        }

        return $attributes;
    }

    public function __serialize(): array
    {
        return $this->getAttributes();
    }

    public function __unserialize(array $data): void
    {
        (function (array $attributes) {
            foreach ($attributes as $key => $value) {
                $this->{$key} = $this->prepareValueAfterUnserialization($key, $value);
            }
        })->call($this, $data);
    }

    protected function handleOutgoingMessage(BasicOutgoingMessageInterface $message): BasicOutgoingMessageInterface
    {
        if (! $message->getContext()) {
            $message->setContext($this->messageContext);
        }

        $sentMessage = call_user_func($this->outgoingCallback, $message);

        if ($sentMessage instanceof OutgoingMessageInterface) {
            $keyboard = $sentMessage->getKeyboard();

            if ($keyboard instanceof SimpleKeyboardInterface) {
                $this->lastKeyboard = $keyboard;
            } else {
                $this->lastKeyboard = null;
            }

            foreach ($keyboard?->getButtons() ?? [] as $button) {
                if ($button instanceof BackButtonInterface) {
                    $this->keepPrevPageReferenceAfterTransition = true;
                }
            }
        }

        return $sentMessage;
    }

    /**
     * @phpstan-template T of BasicOutgoingMessageInterface
     *
     * @phpstan-param T $message
     *
     * @phpstan-return T
     */
    protected function reply(BasicOutgoingMessageInterface $message): BasicOutgoingMessageInterface
    {
        $message->setId(null);

        return $this->handleOutgoingMessage($message);
    }

    /**
     * @phpstan-template T of BasicOutgoingMessageInterface
     *
     * @phpstan-param T $message
     *
     * @phpstan-return T
     */
    protected function updateMessage(BasicOutgoingMessageInterface $message): BasicOutgoingMessageInterface
    {
        if (! $message->getId()) {
            throw new InvalidArgumentException('Message id is required for update');
        }

        return $this->handleOutgoingMessage($message);
    }
}

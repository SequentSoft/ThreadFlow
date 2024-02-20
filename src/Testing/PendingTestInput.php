<?php

namespace SequentSoft\ThreadFlow\Testing;

use Closure;
use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\CommonIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Contracts\Testing\ResultsRecorderInterface;
use SequentSoft\ThreadFlow\Keyboard\Button;
use SequentSoft\ThreadFlow\Messages\Incoming\Regular\ClickIncomingMessage;
use SequentSoft\ThreadFlow\Messages\Incoming\Regular\ContactIncomingMessage;
use SequentSoft\ThreadFlow\Messages\Incoming\Regular\LocationIncomingMessage;

class PendingTestInput
{
    protected ?Closure $textMessageResolver = null;

    protected ?PageInterface $page = null;

    protected ?MessageContextInterface $context = null;

    protected array $sessionAttributes = [];

    public function __construct(
        protected string $channelName,
        protected Closure $run,
    ) {
    }

    public function getContext(): MessageContextInterface
    {
        if ($this->context) {
            return $this->context;
        }

        return MessageContext::createFromIds($this->channelName, 'test-participant', 'test-chat');
    }

    public function withContext(MessageContextInterface|string $context): static
    {
        $this->context = is_string($context)
            ? MessageContext::createFromIds($this->channelName, $context)
            : $context;

        return $this;
    }

    public function withPage(string|PageInterface|null $page = null, array $attributes = []): static
    {
        if (! $page) {
            $this->page = null;
            return $this;
        }

        $this->page = is_string($page)
            ? new $page(...$attributes)
            : $page;

        return $this;
    }

    public function withSessionAttributes(array $sessionAttributes): static
    {
        $this->sessionAttributes = $sessionAttributes;

        return $this;
    }

    public function setTextMessageResolver(Closure $textMessageResolver): static
    {
        $this->textMessageResolver = $textMessageResolver;

        return $this;
    }

    protected function resolveTextMessage(
        string $text,
        MessageContextInterface $context
    ): CommonIncomingMessageInterface {
        if (!$this->textMessageResolver) {
            throw new \RuntimeException('Text message resolver is not set.');
        }

        return call_user_func($this->textMessageResolver, $text, $context)
            ->setContext($context);
    }

    protected function run(CommonIncomingMessageInterface $message): ResultsRecorderInterface
    {
        return call_user_func($this->run, function (SessionInterface $session) use ($message) {
            if ($this->page) {
                $session->setCurrentPage($this->page);
                $this->page->setContext($message->getContext());
            }

            foreach ($this->sessionAttributes as $key => $value) {
                $session->set($key, $value);
            }

            return $session;
        }, $message);
    }

    public function contact(
        string $phoneNumber,
        string $firstName = '',
        string $lastName = '',
        string $userId = ''
    ): ResultsRecorderInterface {
        $message = ContactIncomingMessage::make(
            phoneNumber: $phoneNumber,
            firstName: $firstName,
            lastName: $lastName,
            userId: $userId,
        );

        $message->setContext($this->getContext());

        return $this->run($message);
    }

    public function click(string $key): ResultsRecorderInterface
    {
        $message = ClickIncomingMessage::make(
            button: Button::text($key, $key),
        );

        $message->setContext($this->getContext());

        return $this->run($message);
    }

    public function location(float $latitude, float $longitude): ResultsRecorderInterface
    {
        $message = LocationIncomingMessage::make(
            latitude: $latitude,
            longitude: $longitude,
        );

        $message->setContext($this->getContext());

        return $this->run($message);
    }

    public function input(string|CommonIncomingMessageInterface|Closure $message): ResultsRecorderInterface
    {
        if ($message instanceof Closure) {
            $message = $message($this->getContext());
        }

        $message = is_string($message)
            ? $this->resolveTextMessage($message, $this->getContext())
            : $message;

        return $this->run($message);
    }
}

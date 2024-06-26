<?php

namespace SequentSoft\ThreadFlow\Testing;

use Closure;
use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\BasicIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Contracts\Testing\ResultsRecorderInterface;
use SequentSoft\ThreadFlow\Keyboard\Button;
use SequentSoft\ThreadFlow\Messages\Incoming\Regular\ClickIncomingMessage;
use SequentSoft\ThreadFlow\Messages\Incoming\Regular\ContactIncomingMessage;
use SequentSoft\ThreadFlow\Messages\Incoming\Regular\LocationIncomingMessage;
use SequentSoft\ThreadFlow\Messages\Incoming\Regular\TextIncomingMessage;

use function Laravel\Prompts\text;

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

    /**
     * Set the message context for the test input.
     * If the context is a string, it will be used as the participant ID.
     */
    public function withContext(MessageContextInterface|string $context): static
    {
        $this->context = is_string($context)
            ? MessageContext::createFromIds($this->channelName, $context)
            : $context;

        return $this;
    }

    /**
     * Set the current page for the test input.
     * If the page is a string, it will be used as the page class name.
     */
    public function withPage(string|PageInterface|null $page = null, array $attributes = []): static
    {
        if (! $page) {
            $this->page = null;
            return $this;
        }

        if (is_string($page)) {
            $this->page = new $page(...$attributes);
            return $this;
        }

        $this->page = $page;

        // fill page properties
        // private and protected properties are not accessible from outside the class,
        // so we need to use a closure to access them
        (function () use ($attributes) {
            foreach ($attributes as $key => $value) {
                $this->{$key} = $value;
            }
        })->call($page);

        return $this;
    }

    /**
     * Set the session attributes for the test input.
     * The attributes will be set to the session before the test input is run.
     */
    public function withSessionAttributes(array $sessionAttributes): static
    {
        $this->sessionAttributes = $sessionAttributes;

        return $this;
    }

    protected function run(BasicIncomingMessageInterface $message): ResultsRecorderInterface
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

    /**
     * Send a contact message to the fake channel.
     */
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

    /**
     * Click a button in the fake channel.
     */
    public function click(string $key): ResultsRecorderInterface
    {
        $message = ClickIncomingMessage::make(
            button: Button::text($key, $key),
        );

        $message->setContext($this->getContext());

        return $this->run($message);
    }

    /**
     * Send a location message to the fake channel.
     */
    public function location(float $latitude, float $longitude): ResultsRecorderInterface
    {
        $message = LocationIncomingMessage::make(
            latitude: $latitude,
            longitude: $longitude,
        );

        $message->setContext($this->getContext());

        return $this->run($message);
    }

    /**
     * Send a text message or a message instance of any type to the fake channel.
     */
    public function input(string|BasicIncomingMessageInterface|Closure $message): ResultsRecorderInterface
    {
        if ($message instanceof Closure) {
            $message = $message($this->getContext());
        }

        $message = is_string($message)
            ? TextIncomingMessage::make(text: $message, id: 'test', context: $this->getContext())
            : $message;

        return $this->run($message);
    }
}

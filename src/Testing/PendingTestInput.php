<?php

namespace SequentSoft\ThreadFlow\Testing;

use Closure;
use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Contracts\Testing\ResultsRecorderInterface;
use SequentSoft\ThreadFlow\Session\PageState;

class PendingTestInput
{
    protected ?Closure $textMessageResolver = null;

    protected ?PageStateInterface $state = null;

    protected ?MessageContextInterface $context = null;

    protected array $sessionAttributes = [];

    public function __construct(
        protected Closure $run,
    ) {
    }

    public function getContext(): MessageContextInterface
    {
        if ($this->context) {
            return $this->context;
        }

        return MessageContext::createFromIds('test-participant', 'test-chat');
    }

    public function withContext(MessageContextInterface|string $context): static
    {
        $this->context = is_string($context)
            ? MessageContext::createFromIds($context)
            : $context;

        return $this;
    }

    public function withState(string|PageStateInterface|null $state = null, array $attributes = []): static
    {
        if (! $state) {
            $this->state = null;
            return $this;
        }

        $this->state = is_string($state)
            ? PageState::create($state, $attributes)
            : $state;

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

    protected function resolveTextMessage(string $text, MessageContextInterface $context): IncomingMessageInterface
    {
        if (! $this->textMessageResolver) {
            throw new \RuntimeException('Text message resolver is not set.');
        }

        return call_user_func($this->textMessageResolver, $text, $context);
    }

    public function input(string|IncomingMessageInterface|Closure $message): ResultsRecorderInterface
    {
        if ($message instanceof Closure) {
            $message = $message($this->getContext());
        }

        $message = is_string($message)
            ? $this->resolveTextMessage($message, $this->getContext())
            : $message;

        return call_user_func($this->run, function (SessionInterface $session) {
            if ($this->state) {
                $session->setPageState($this->state);
            }

            foreach ($this->sessionAttributes as $key => $value) {
                $session->set($key, $value);
            }
        }, $message);
    }
}

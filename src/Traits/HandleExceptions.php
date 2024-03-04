<?php

namespace SequentSoft\ThreadFlow\Traits;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\CommonIncomingMessageInterface as IMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use Throwable;

trait HandleExceptions
{
    protected bool $exceptionsHandlersEnabled = true;

    protected array $exceptionsHandlers = [];

    public function getExceptionsHandlers(): array
    {
        return $this->exceptionsHandlers;
    }

    public function registerExceptionHandler(Closure $callback): void
    {
        $this->exceptionsHandlers[] = $callback;
    }

    public function disableExceptionsHandlers(): void
    {
        $this->exceptionsHandlersEnabled = false;
    }

    /**
     * @throws Throwable
     */
    protected function handleException(
        Throwable $exception,
        SessionInterface $session,
        MessageContextInterface $messageContext,
        ?IMessageInterface $message = null,
    ): void {
        if (! $this->exceptionsHandlersEnabled) {
            throw $exception;
        }

        if (count($this->exceptionsHandlers) === 0) {
            throw $exception;
        }

        foreach ($this->exceptionsHandlers as $handler) {
            $handler($exception, $session, $messageContext, $message);
        }
    }
}

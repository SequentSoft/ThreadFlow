<?php

namespace SequentSoft\ThreadFlow\Traits;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use Throwable;

trait HandleExceptions
{
    protected bool $exceptionsHandlersEnabled = true;

    protected array $exceptionsHandlers = [];

    // array of exceptions to handle later (delayed)
    protected array $exceptionsToHandle = [];

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

    protected function handleExceptionsLater(
        Throwable $exception,
        MessageContextInterface $messageContext,
    ): void {
        $this->exceptionsToHandle[] = [$exception, $messageContext];
    }

    /**
     * @throws Throwable
     */
    protected function handleExceptionsNow(): void
    {
        foreach ($this->exceptionsToHandle as $exceptionToHandle) {
            [$exception, $messageContext] = $exceptionToHandle;
            $this->handleException($exception, $messageContext);
        }
    }

    /**
     * @throws Throwable
     */
    protected function handleException(
        Throwable $exception,
        MessageContextInterface $messageContext,
    ): void {
        if (! $this->exceptionsHandlersEnabled) {
            throw $exception;
        }

        if (count($this->exceptionsHandlers) === 0) {
            throw $exception;
        }

        foreach ($this->exceptionsHandlers as $handler) {
            $handler($exception, $messageContext);
        }
    }
}

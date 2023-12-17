<?php

namespace SequentSoft\ThreadFlow\Traits;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface as IMessageInterface;
use Throwable;

trait HandleExceptions
{
    protected array $exceptionsHandlers = [];

    public function registerExceptionHandler(Closure $callback): void
    {
        $this->exceptionsHandlers[] = $callback;
    }

    /**
     * @throws Throwable
     */
    protected function handleException(
        string $channelName,
        Throwable $exception,
        SessionInterface $session,
        MessageContextInterface $messageContext,
        ?IMessageInterface $message = null,
    ): void {
        if (count($this->exceptionsHandlers) === 0) {
            throw $exception;
        }

        foreach ($this->exceptionsHandlers as $handler) {
            $handler($channelName, $exception, $session, $messageContext, $message);
        }
    }
}

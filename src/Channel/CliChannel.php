<?php

namespace SequentSoft\ThreadFlow\Channel;

use Closure;
use DateTimeImmutable;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\DataFetchers\DataFetcherInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\CommonIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\CommonOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class CliChannel extends Channel
{
    protected ?Closure $callback = null;

    public function setCallback(Closure $callback): void
    {
        $this->callback = $callback;
    }

    public function listen(MessageContextInterface $messageContext, DataFetcherInterface $fetcher): void
    {
        $fetcher->fetch(function (array $update) use ($messageContext) {
            $message = $update['message'] ?? '';

            if ($message instanceof CommonIncomingMessageInterface) {
                $this->incoming($message);
                return;
            }

            $this->incoming(
                $this->makeIncomingMessageFromText(
                    $update['id'],
                    $message,
                    new DateTimeImmutable(),
                    $messageContext,
                )
            );
        });
    }

    protected function outgoing(
        CommonOutgoingMessageInterface $message,
        ?SessionInterface $session,
        ?PageInterface $contextPage
    ): CommonOutgoingMessageInterface {
        if ($this->callback !== null) {
            return call_user_func($this->callback, $message, $session, $contextPage);
        }

        return $message;
    }
}

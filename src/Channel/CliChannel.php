<?php

namespace SequentSoft\ThreadFlow\Channel;

use Closure;
use DateTimeImmutable;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\DataFetchers\DataFetcherInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface;
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
        $fetcher->fetch(fn (array $update) => $this->incoming(
            $this->makeIncomingMessageFromText(
                $update['id'],
                $update['text'] ?? '',
                new DateTimeImmutable(),
                $messageContext,
            )
        ));
    }

    protected function outgoing(
        OutgoingMessageInterface $message,
        ?SessionInterface $session,
        ?PageInterface $contextPage
    ): OutgoingMessageInterface {
        if ($this->callback !== null) {
            return call_user_func($this->callback, $message, $session, $contextPage);
        }

        return $message;
    }
}

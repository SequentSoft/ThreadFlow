<?php

namespace SequentSoft\ThreadFlow\Channel\Incoming;

use Closure;
use DateTimeImmutable;
use SequentSoft\ThreadFlow\Contracts\Channel\Incoming\IncomingChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\DataFetchers\DataFetcherInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Messages\Incoming\Regular\TextIncomingRegularMessage;

class CliIncomingChannel implements IncomingChannelInterface
{
    public function __construct(
        protected MessageContextInterface $messageContext,
    ) {
    }

    public function listen(DataFetcherInterface $fetcher, Closure $callback): void
    {
        $fetcher->fetch(fn (array $update) => $callback(
            new TextIncomingRegularMessage(
                $update['id'],
                $this->messageContext,
                new DateTimeImmutable(),
                $update['text'] ?? '',
            )
        ));
    }

    public function preprocess(IncomingMessageInterface $message, SessionInterface $session): IncomingMessageInterface
    {
        return $message;
    }
}

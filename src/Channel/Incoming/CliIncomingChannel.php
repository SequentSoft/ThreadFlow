<?php

namespace SequentSoft\ThreadFlow\Channel\Incoming;

use Closure;
use DateTimeImmutable;
use SequentSoft\ThreadFlow\Contracts\Channel\Incoming\IncomingChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\SimpleConfigInterface;
use SequentSoft\ThreadFlow\Contracts\DataFetchers\DataFetcherInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Messages\Incoming\Regular\TextIncomingRegularMessage;

class CliIncomingChannel implements IncomingChannelInterface
{
    public function __construct(
        protected MessageContextInterface $messageContext,
        protected SimpleConfigInterface $config,
    ) {
    }

    public function listen(DataFetcherInterface $fetcher, Closure $callback): void
    {
        $fetcher->fetch(fn(array $update) => $callback(
            $this->makeMessageFromText(
                $update['id'],
                $update['text'] ?? '',
                new DateTimeImmutable(),
                $this->messageContext,
            )->setContext($this->messageContext)
        ));
    }

    public function preprocess(
        IncomingMessageInterface $message,
        SessionInterface $session,
        PageStateInterface $pageState,
    ): IncomingMessageInterface {
        return $message;
    }

    public function makeMessageFromText(
        string $id,
        string $text,
        DateTimeImmutable $date,
        MessageContextInterface $context
    ): ?IncomingMessageInterface {
        return (new TextIncomingRegularMessage(
            $id,
            $this->messageContext,
            $date,
            $text,
        ))->setContext($context);
    }
}

<?php

use SequentSoft\ThreadFlow\Channel\Incoming\IncomingChannelRegistry;
use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Config;
use SequentSoft\ThreadFlow\Contracts\Channel\Incoming\IncomingChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Incoming\IncomingChannelRegistryInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\DataFetchers\DataFetcherInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Exceptions\Channel\ChannelNotConfiguredException;
use SequentSoft\ThreadFlow\Exceptions\Channel\ChannelNotFoundException;
use SequentSoft\ThreadFlow\Messages\Incoming\Regular\TextIncomingRegularMessage;

it('can be created', function () {
    $registry = new IncomingChannelRegistry();

    expect($registry)->toBeInstanceOf(IncomingChannelRegistryInterface::class);
});

it('can register channel', function () {
    $registry = new IncomingChannelRegistry();

    $registry->register('test-channel-driver', function (ConfigInterface $config) {
        return new class ($config) implements IncomingChannelInterface {
            public function __construct(protected ConfigInterface $config)
            {
            }

            public function getConfig(): ConfigInterface
            {
                return $this->config;
            }

            public function listen(DataFetcherInterface $fetcher, Closure $callback): void
            {
            }

            public function makeMessageFromText(
                string $id,
                string $text,
                DateTimeImmutable $date,
                MessageContextInterface $context
            ): ?IncomingMessageInterface {
                return (new TextIncomingRegularMessage(
                    $id,
                    MessageContext::createFromIds(1, 1),
                    $date,
                    $text,
                ))->setContext($context);
            }

            public function preprocess(
                IncomingMessageInterface $message,
                SessionInterface $session,
                PageStateInterface $pageState
            ): IncomingMessageInterface {
                return $message;
            }
        };
    });

    $channel = $registry->get('test-channel', new Config([
        'driver' => 'test-channel-driver',
    ]));

    expect($channel)->toBeInstanceOf(IncomingChannelInterface::class);
});

it('can throw exception if channel driver is not registered', function () {
    $registry = new IncomingChannelRegistry();

    $registry->get('test-channel', new Config([
        'driver' => 'test-channel-driver',
    ]));
})->throws(ChannelNotFoundException::class);

it('can throw exception if channel is not configured', function () {
    $registry = new IncomingChannelRegistry();

    $registry->get('test-channel', new Config([]));
})->throws(ChannelNotConfiguredException::class);

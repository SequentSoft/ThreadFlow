<?php

use SequentSoft\ThreadFlow\Channel\Outgoing\OutgoingChannelRegistry;
use SequentSoft\ThreadFlow\Config;
use SequentSoft\ThreadFlow\Contracts\Channel\Outgoing\OutgoingChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Outgoing\OutgoingChannelRegistryInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Exceptions\Channel\ChannelNotConfiguredException;
use SequentSoft\ThreadFlow\Exceptions\Channel\ChannelNotFoundException;

it('can be created', function () {
    $registry = new OutgoingChannelRegistry();

    expect($registry)->toBeInstanceOf(OutgoingChannelRegistryInterface::class);
});

it('can register channel driver', function () {
    $registry = new OutgoingChannelRegistry();

    $registry->register('test-channel-driver', function (ConfigInterface $config) {
        return new class ($config) implements OutgoingChannelInterface {
            public function __construct(protected ConfigInterface $config)
            {
            }

            public function getConfig(): ConfigInterface
            {
                return $this->config;
            }

            public function send(
                OutgoingMessageInterface $message,
                SessionInterface $session,
                ?PageInterface $contextPage = null
            ): OutgoingMessageInterface {
                return $message;
            }
        };
    });

    $channel = $registry->get('test-channel', new Config([
        'driver' => 'test-channel-driver',
    ]));

    expect($channel)->toBeInstanceOf(OutgoingChannelInterface::class);
});

it('can throw exception if channel driver is not registered', function () {
    $registry = new OutgoingChannelRegistry();

    $registry->get('test-channel', new Config([
        'driver' => 'test-channel-driver',
    ]));
})->throws(ChannelNotFoundException::class);

it('can throw exception if channel driver is not configured', function () {
    $registry = new OutgoingChannelRegistry();

    $registry->get('test-channel', new Config([
        //
    ]));
})->throws(ChannelNotConfiguredException::class);

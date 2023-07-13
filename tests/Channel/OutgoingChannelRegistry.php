<?php

use SequentSoft\ThreadFlow\Channel\Outgoing\OutgoingChannelRegistry;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Outgoing\OutgoingChannelInterface;
use SequentSoft\ThreadFlow\Exceptions\Channel\ChannelNotFoundException;

it('registers and retrieves a driver', function () {
    $registry = new OutgoingChannelRegistry();

    $configMock = $this->createMock(ConfigInterface::class);
    $configMock->method('get')
        ->with('driver')
        ->willReturn('testDriver');

    $testChannel = $this->createMock(OutgoingChannelInterface::class);

    $callback = function () use ($testChannel) {
        return $testChannel;
    };

    $registry->register('testDriver', $callback);

    $retrievedChannel = $registry->get('testDriver', $configMock);

    expect($retrievedChannel)->toBe($testChannel);
});

it('throws an exception when trying to retrieve an unregistered driver', function () {
    $registry = new OutgoingChannelRegistry();

    $configMock = $this->createMock(ConfigInterface::class);
    $configMock->method('get')
        ->with('driver')
        ->willReturn('unregisteredDriver');

    $registry->get('unregisteredDriver', $configMock);
})->throws(ChannelNotFoundException::class);

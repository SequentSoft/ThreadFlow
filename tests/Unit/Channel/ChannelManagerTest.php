<?php

use SequentSoft\ThreadFlow\ChannelManager;
use SequentSoft\ThreadFlow\Contracts\Channel\ChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;

beforeEach(function () {
    $this->config = Mockery::mock(ConfigInterface::class);
    $this->sessionStoreFactory = Mockery::mock(SessionStoreFactoryInterface::class);
    $this->dispatcherFactory = Mockery::mock(DispatcherFactoryInterface::class);
    $this->eventBus = Mockery::mock(EventBusInterface::class);

    $this->channelManager = new ChannelManager(
        $this->config,
        $this->sessionStoreFactory,
        $this->dispatcherFactory,
        $this->eventBus
    );
});

test('registerChannelDriver registers a channel driver', function () {
    $channelName = 'testChannel';
    $callback = function () {
    };

    $this->channelManager->registerChannelDriver($channelName, $callback);

    $registeredDrivers = $this->channelManager->getRegisteredChannelDrivers();

    expect($registeredDrivers)->toHaveKey($channelName);
    expect($registeredDrivers[$channelName])->toBe($callback);
});

test('makeChannel creates a channel successfully', function () {
    $channelName = 'testChannel';
    $channel = Mockery::mock(ChannelInterface::class);
    $nestedEventBus = Mockery::mock(EventBusInterface::class);
    $callback = function () use ($channel) {
        return $channel;
    };

    $channel->shouldReceive('registerExceptionHandler')->with(Mockery::type('closure'))->once();

    $this->channelManager->registerChannelDriver($channelName, $callback);
    $this->config->shouldReceive('get')->with('session')->once()->andReturn('array');
    $this->config->shouldReceive('getNested')->with('channels')->andReturn($this->config);
    $this->config->shouldReceive('getNested')->with($channelName)->andReturn($this->config);
    $this->sessionStoreFactory->shouldReceive('make')->andReturn(Mockery::mock(SessionStoreInterface::class));

    $this->eventBus->shouldReceive('nested')->with($channelName)->once()->andReturn($nestedEventBus);

    $createdChannel = $this->channelManager->channel($channelName);

    expect($createdChannel)->toBe($channel);
});

test('channel throws exception if driver not registered', function () {
    $channelName = 'nonExistentChannel';

    $this->expectException(RuntimeException::class);
    $this->channelManager->channel($channelName);
});

test('on method registers event listener', function () {
    $event = 'testEvent';
    $callback = function () {
    };

    $this->eventBus->shouldReceive('listen')->with($event, $callback)->once();

    $this->channelManager->on($event, $callback);
});

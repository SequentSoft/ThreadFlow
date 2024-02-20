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
    $this->channelsConfig = Mockery::mock(ConfigInterface::class);
    $this->channelConfig = Mockery::mock(ConfigInterface::class);

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
    $driverName = 'testChannelDriver';
    $channel = Mockery::mock(ChannelInterface::class);
    $nestedEventBus = Mockery::mock(EventBusInterface::class);
    $callback = function () use ($channel) {
        return $channel;
    };

    $channel->shouldReceive('registerExceptionHandler')->with(Mockery::type('closure'))->once();
    $channel->shouldReceive('setUserResolver')->with(Mockery::type('null'))->once();

    $this->channelManager->registerChannelDriver($driverName, $callback);
    $this->config->shouldReceive('getNested')->with('channels')->andReturn($this->channelsConfig);
    $this->channelsConfig->shouldReceive('getNested')->with($driverName)->andReturn($this->channelConfig);
    $this->channelConfig->shouldReceive('get')->with('session')->once()->andReturn('array');
    $this->channelConfig->shouldReceive('get')->with('driver')->once()->andReturn($driverName);

    $this->sessionStoreFactory->shouldReceive('make')->andReturn(Mockery::mock(SessionStoreInterface::class));

    $this->eventBus->shouldReceive('nested')->with($driverName)->once()->andReturn($nestedEventBus);

    $createdChannel = $this->channelManager->channel($driverName);
    $theSameChannel = $this->channelManager->channel($driverName);

    expect($createdChannel)->toBe($theSameChannel)
        ->and($createdChannel)->toBe($channel);
});

test('channel throws exception if driver not registered', function () {
    $driverName = 'nonExistentChannel';

    $this->config->shouldReceive('getNested')->with('channels')->andReturn($this->channelsConfig);
    $this->channelsConfig->shouldReceive('getNested')->with($driverName)->andReturn($this->channelConfig);
    $this->channelConfig->shouldReceive('get')->with('driver')->once()->andReturn($driverName);

    $this->expectException(RuntimeException::class);
    $this->channelManager->channel($driverName);
});

test('on method registers event listener', function () {
    $event = 'testEvent';
    $callback = function () {
    };

    $this->eventBus->shouldReceive('listen')->with($event, $callback)->once();

    $this->channelManager->on($event, $callback);
});

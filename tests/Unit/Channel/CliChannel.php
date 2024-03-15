<?php

use SequentSoft\ThreadFlow\Channel\CliChannel;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\DataFetchers\DataFetcherInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;
use SequentSoft\ThreadFlow\Dispatcher\SyncDispatcher;
use SequentSoft\ThreadFlow\Events\Bot\SessionClosedEvent;
use SequentSoft\ThreadFlow\Events\Bot\SessionStartedEvent;
use SequentSoft\ThreadFlow\Events\Message\IncomingMessageDispatchingEvent;
use SequentSoft\ThreadFlow\Session\Session;

beforeEach(function () {
    $this->config = Mockery::mock(ConfigInterface::class);
    $this->sessionStore = Mockery::mock(SessionStoreInterface::class);
    $this->dispatcherFactory = Mockery::mock(DispatcherFactoryInterface::class);
    $this->eventBus = Mockery::mock(EventBusInterface::class);
    $this->syncDispatcher = Mockery::mock(SyncDispatcher::class);

    $this->channel = new CliChannel(
        'testChannel',
        $this->config,
        $this->sessionStore,
        $this->dispatcherFactory,
        $this->eventBus
    );
});

test('listen method processes incoming messages correctly', function () {
    $messageContext = Mockery::mock(MessageContextInterface::class);
    $fetcher = Mockery::mock(DataFetcherInterface::class);
    $update = ['id' => '123', 'text' => 'Hello World'];

    $fetcher->shouldReceive('fetch')->once()->andReturnUsing(function ($closure) use ($update) {
        $closure($update);
    });

    $this->sessionStore->shouldReceive('useSession')->with($messageContext, Mockery::type('closure'))->once()->andReturnUsing(function ($context, $closure) {
        return $closure(new Session());
    });

    $this->eventBus->shouldReceive('fire')->with(Mockery::type(IncomingMessageDispatchingEvent::class))->once();
    $this->eventBus->shouldReceive('fire')->with(Mockery::type(SessionStartedEvent::class))->once();
    $this->eventBus->shouldReceive('fire')->with(Mockery::type(SessionClosedEvent::class))->once();
    $this->config->shouldReceive('get')->with('dispatcher')->once()->andReturn('sync');
    $this->config->shouldReceive('get')->with('entry')->once()->andReturn(\Tests\Stubs\EmptyPage::class);
    $this->dispatcherFactory->shouldReceive('make')->once()->andReturn($this->syncDispatcher);
    $this->syncDispatcher->shouldReceive('incoming')->once();

    $this->channel->listen($messageContext, $fetcher);
});

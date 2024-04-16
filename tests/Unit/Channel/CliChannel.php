<?php

use SequentSoft\ThreadFlow\Channel\CliChannel;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\DataFetchers\DataFetcherInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;
use SequentSoft\ThreadFlow\Events\Bot\SessionClosedEvent;
use SequentSoft\ThreadFlow\Events\Bot\SessionStartedEvent;
use SequentSoft\ThreadFlow\Session\Session;

beforeEach(function () {
    $this->config = Mockery::mock(ConfigInterface::class);
    $this->sessionStore = Mockery::mock(SessionStoreInterface::class);
    $this->dispatcher = Mockery::mock(DispatcherInterface::class);
    $this->eventBus = Mockery::mock(EventBusInterface::class);

    $this->dispatcher->shouldReceive('setOutgoingCallback')->with(Mockery::type('closure'))->once();

    $this->channel = new CliChannel(
        'testChannel',
        $this->config,
        $this->sessionStore,
        $this->dispatcher,
        $this->eventBus
    );
});

test('listen method processes incoming messages correctly', function () {
    $messageContext = Mockery::mock(MessageContextInterface::class);
    $fetcher = Mockery::mock(DataFetcherInterface::class);
    $update = ['id' => '123', 'text' => 'Hello World'];

    $entryPage = Mockery::mock(PageInterface::class);

    $entryPage->shouldReceive('getLastKeyboard')->once()->andReturnNull();

    $fetcher->shouldReceive('fetch')->once()->andReturnUsing(function ($closure) use ($update) {
        $closure($update);
    });

    $this->sessionStore
        ->shouldReceive('useSession')
        ->with($messageContext, Mockery::type('closure'))
        ->once()
        ->andReturnUsing(function ($context, $closure) {
            return $closure(new Session());
        });

    $this->eventBus->shouldReceive('fire')->with(Mockery::type(SessionStartedEvent::class))->once();
    $this->eventBus->shouldReceive('fire')->with(Mockery::type(SessionClosedEvent::class))->once();
    $this->config->shouldReceive('get')->with('entry')->once()->andReturn($entryPage);
    $this->dispatcher->shouldReceive('incoming')->once();

    $this->channel->listen($messageContext, $fetcher);
});

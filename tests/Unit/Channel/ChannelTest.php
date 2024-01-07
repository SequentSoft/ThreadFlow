<?php

use SequentSoft\ThreadFlow\Channel\Channel;
use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Contracts\Channel\ChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;
use SequentSoft\ThreadFlow\Events\Message\IncomingMessageDispatchingEvent;
use SequentSoft\ThreadFlow\Testing\ResultsRecorder;

beforeEach(function () {
    $this->config = Mockery::mock(ConfigInterface::class);
    $this->sessionStore = Mockery::mock(SessionStoreInterface::class);
    $this->dispatcherFactory = Mockery::mock(DispatcherFactoryInterface::class);
    $this->eventBus = Mockery::mock(EventBusInterface::class);

    $this->channel = new class('testChannel', $this->config, $this->sessionStore, $this->dispatcherFactory, $this->eventBus) extends Channel
    {
        protected function outgoing(OutgoingMessageInterface $message, ?SessionInterface $session, ?PageInterface $contextPage): OutgoingMessageInterface
        {
            // Mocked implementation
        }
    };
});

test('channel implements ChannelInterface', function () {
    expect($this->channel)->toBeInstanceOf(ChannelInterface::class);
});

test('getConfig returns correct config instance', function () {
    expect($this->channel->getConfig())->toBe($this->config);
});

test('incoming message triggers correct sequence of actions', function () {
    $message = Mockery::mock(IncomingMessageInterface::class);
    $session = Mockery::mock(SessionInterface::class);
    $dispatcher = Mockery::mock(DispatcherInterface::class);
    $messageContext = MessageContext::createFromIds('test');

    $message->shouldReceive('getContext')->andReturn($messageContext);

    $this->sessionStore->shouldReceive('useSession')->with($messageContext, Mockery::type('closure'))->once()->andReturnUsing(function ($context, $closure) use ($session) {
        return $closure($session);
    });

    $this->dispatcherFactory->shouldReceive('make')->andReturn($dispatcher);

    $dispatcher->shouldReceive('incoming')->with($message, $session)->once();

    $this->eventBus->shouldReceive('fire')->with(Mockery::type(IncomingMessageDispatchingEvent::class))->once();

    $this->config->shouldReceive('get')->with('dispatcher')->once()->andReturn('sync');

    $this->channel->incoming($message);
});

test('showPage handles context and page correctly', function () {
    $messageContext = MessageContext::createFromIds('test');
    $page = 'test-page';
    $pageAttributes = ['key' => 'value'];
    $session = Mockery::mock(SessionInterface::class);
    $dispatcher = Mockery::mock(DispatcherInterface::class);

    $this->config->shouldReceive('get')->with('dispatcher')->once()->andReturn('sync');

    $this->sessionStore->shouldReceive('useSession')->andReturnUsing(function ($context, $closure) use ($session) {
        $closure($session);
    });
    $this->dispatcherFactory->shouldReceive('make')->andReturn($dispatcher);
    $dispatcher->shouldReceive('transition')->once();

    $this->channel->showPage($messageContext, $page, $pageAttributes);
});

test('sendMessage returns correct OutgoingMessageInterface instance', function () {
    $messageContext = MessageContext::createFromIds('test');
    $textMessage = 'Hello World';
    $dispatcher = Mockery::mock(DispatcherInterface::class);

    $this->config->shouldReceive('get')->andReturn('sync');
    $this->dispatcherFactory->shouldReceive('make')->andReturn($dispatcher);
    $dispatcher->shouldReceive('outgoing')->andReturn(Mockery::mock(OutgoingMessageInterface::class));

    $sentMessage = $this->channel->sendMessage($messageContext, $textMessage);

    expect($sentMessage)->toBeInstanceOf(OutgoingMessageInterface::class);
});

test('testInput captures correct ResultsRecorder instance', function () {
    $input = 'Test input';
    $this->eventBus->shouldReceive('setListeners')->once()->andReturnSelf();
    $this->eventBus->shouldReceive('getListeners')->once()->andReturn([]);
    $this->eventBus->shouldReceive('listen')->andReturnSelf();

    $this->sessionStore->shouldReceive('useSession')->once();

    $results = $this->channel->testInput($input);

    expect($results)->toBeInstanceOf(ResultsRecorder::class);
});

test('fakeMessageContext creates correct MessageContextInterface instance', function () {
    $context = $this->channel->fakeMessageContext();

    expect($context)->toBeInstanceOf(MessageContextInterface::class);
    expect($context->getParticipant()->getId())->toEqual('test-participant');
    expect($context->getRoom()->getId())->toEqual('test-chat');
});

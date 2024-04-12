<?php

use SequentSoft\ThreadFlow\Builders\ChannelPendingSend;
use SequentSoft\ThreadFlow\Channel\Channel;
use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Contracts\Channel\ChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\ParticipantInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\RoomInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\BasicIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Service\BotStartedIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\BasicOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\TextOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\ActivePagesStorageFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\PendingMessages\PendingMessagesStorageFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;
use SequentSoft\ThreadFlow\Contracts\Testing\ResultsRecorderInterface;
use SequentSoft\ThreadFlow\Events\Bot\SessionClosedEvent;
use SequentSoft\ThreadFlow\Events\Bot\SessionStartedEvent;
use SequentSoft\ThreadFlow\Events\Message\IncomingMessageDispatchingEvent;

beforeEach(function () {
    $this->config = Mockery::mock(ConfigInterface::class);
    $this->sessionStore = Mockery::mock(SessionStoreInterface::class);
    $this->dispatcher = Mockery::mock(DispatcherInterface::class);
    $this->eventBus = Mockery::mock(EventBusInterface::class);
    $this->activePagesStorageFactory = Mockery::mock(ActivePagesStorageFactoryInterface::class);
    $this->pendingMessagesStorageFactory = Mockery::mock(PendingMessagesStorageFactoryInterface::class);

    $this->dispatcher->shouldReceive('setOutgoingCallback')->with(Mockery::type('closure'))->once();

    $this->channel = new class('testChannel', $this->config, $this->sessionStore, $this->dispatcher, $this->eventBus) extends Channel
    {
        protected function outgoing(BasicOutgoingMessageInterface $message, ?SessionInterface $session, ?PageInterface $contextPage): BasicOutgoingMessageInterface
        {
            return $message;
        }
    };
});

test('channel implements ChannelInterface', function () {
    expect($this->channel)->toBeInstanceOf(ChannelInterface::class);
});

test('getName returns correct channel name', function () {
    expect($this->channel->getName())->toBe('testChannel');
});

test('forParticipant returns ChannelPendingSend with correct participant', function () {
    $participant = 'testParticipant';
    $pendingSend = $this->channel->forParticipant($participant);

    expect($pendingSend)->toBeInstanceOf(ChannelPendingSend::class)
        ->and($pendingSend->getParticipant())->toBeInstanceOf(ParticipantInterface::class);
});

test('forRoom returns ChannelPendingSend with correct room', function () {
    $room = 'testRoom';
    $pendingSend = $this->channel->forRoom($room);

    expect($pendingSend)->toBeInstanceOf(ChannelPendingSend::class)
        ->and($pendingSend->getRoom())->toBeInstanceOf(RoomInterface::class);
});

test('on registers event listener correctly', function () {
    $event = 'testEvent';
    $callback = function () {
    };

    $this->eventBus->shouldReceive('listen')->with($event, $callback)->once();

    $this->channel->on($event, $callback);
});

test('getConfig returns correct config instance', function () {
    expect($this->channel->getConfig())->toBe($this->config);
});

test('incoming message triggers correct sequence of actions', function () {
    $message = Mockery::mock(BasicIncomingMessageInterface::class);
    $session = Mockery::mock(SessionInterface::class);
    $messageContext = MessageContext::createFromIds('test', 'test');
    $page = Mockery::mock(PageInterface::class);
    $session->shouldReceive('getCurrentPage')->andReturn($page);
    $page->shouldReceive('getLastKeyboard')->andReturn(null);

    $message->shouldReceive('getContext')->andReturn($messageContext);

    $this->sessionStore->shouldReceive('useSession')->with($messageContext, Mockery::type('closure'))->once()->andReturnUsing(function ($context, $closure) use ($session) {
        return $closure($session);
    });

    $this->dispatcher->shouldReceive('incoming')->with($message, $session, $page)->once();

    $this->eventBus->shouldReceive('fire')->with(Mockery::type(SessionStartedEvent::class))->once();
    $this->eventBus->shouldReceive('fire')->with(Mockery::type(SessionClosedEvent::class))->once();

    $this->channel->incoming($message);
});

test('incoming bot started message resets session', function () {
    $message = Mockery::mock(BotStartedIncomingMessageInterface::class);
    $session = Mockery::mock(SessionInterface::class);
    $messageContext = MessageContext::createFromIds('test', 'test');

    $page = Mockery::mock(PageInterface::class);
    $session->shouldReceive('getCurrentPage')->andReturn($page);
    $page->shouldReceive('getLastKeyboard')->andReturn(null);

    $message->shouldReceive('getContext')->andReturn($messageContext);

    $this->sessionStore->shouldReceive('useSession')->with($messageContext, Mockery::type('closure'))->once()->andReturnUsing(function ($context, $closure) use ($session) {
        return $closure($session);
    });

    $this->dispatcher->shouldReceive('incoming')->with($message, $session, $page)->once();

    $this->eventBus->shouldReceive('fire')->with(Mockery::type(SessionStartedEvent::class))->once();
    $this->eventBus->shouldReceive('fire')->with(Mockery::type(SessionClosedEvent::class))->once();

    $session->shouldReceive('reset')->once();

    $this->channel->incoming($message);
});

test('dispatchTo handles context and page correctly', function () {
    $session = Mockery::mock(SessionInterface::class);
    $page = Mockery::mock(PageInterface::class);
    $context = Mockery::mock(MessageContext::class);

    $session->shouldReceive('getCurrentPage')->andReturn($page);

    $page->shouldReceive('isDontDisturb')->andReturn(false);
    $page->shouldReceive('setContext')->with($context)->once();

    $this->sessionStore->shouldReceive('useSession')->andReturnUsing(function ($context, $closure) use ($session) {
        $closure($session);
    });

    $this->eventBus->shouldReceive('fire')->with(Mockery::type(SessionStartedEvent::class))->once();
    $this->eventBus->shouldReceive('fire')->with(Mockery::type(SessionClosedEvent::class))->once();
    $this->dispatcher->shouldReceive('transition')->once();

    $this->channel->dispatchTo($context, $page);
});

test('dispatchTo returns correct OutgoingMessageInterface instance', function () {
    $textMessage = Mockery::mock(TextOutgoingMessageInterface::class);
    $context = Mockery::mock(MessageContext::class);
    $session = Mockery::mock(SessionInterface::class);
    $page = Mockery::mock(PageInterface::class);

    $this->config->shouldReceive('get')->andReturn('sync');

    $this->dispatcher->shouldReceive('outgoing')->andReturn(Mockery::mock(BasicOutgoingMessageInterface::class));

    $this->sessionStore->shouldReceive('useSession')->andReturnUsing(function ($context, $closure) use ($session) {
        return $closure($session);
    });

    $this->eventBus->shouldReceive('fire')->with(Mockery::type(SessionStartedEvent::class))->once();
    $this->eventBus->shouldReceive('fire')->with(Mockery::type(SessionClosedEvent::class))->once();

    $textMessage->shouldReceive('setContext')->with($context)->once();
    $session->shouldReceive('getCurrentPage')->andReturn($page);
    $page->shouldReceive('isDontDisturb')->andReturn(false);

    $sentMessage = $this->channel->dispatchTo($context, $textMessage);

    expect($sentMessage)->toBeInstanceOf(BasicOutgoingMessageInterface::class);
});

test('testInput captures correct ResultsRecorder instance', function () {
    $input = 'Test input';
    $this->eventBus->shouldReceive('setListeners')->once()->andReturnSelf();
    $this->eventBus->shouldReceive('getListeners')->once()->andReturn([]);
    $this->eventBus->shouldReceive('listen')->andReturnSelf();

    $this->sessionStore->shouldReceive('useSession')->once();

    $results = $this->channel->test()->input($input);

    expect($results)->toBeInstanceOf(ResultsRecorderInterface::class);
});

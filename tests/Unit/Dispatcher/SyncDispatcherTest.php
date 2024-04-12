<?php

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\BasicIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\BasicOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\ActivePagesRepositoryInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\PendingMessages\PendingMessagesRepositoryInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Dispatcher\SyncDispatcher;
use SequentSoft\ThreadFlow\Events\Message\IncomingMessageDispatchingEvent;
use SequentSoft\ThreadFlow\Events\Message\OutgoingMessageSendingEvent;
use SequentSoft\ThreadFlow\Events\Message\OutgoingMessageSentEvent;
use SequentSoft\ThreadFlow\Events\Page\PageDispatchedEvent;
use SequentSoft\ThreadFlow\Events\Page\PageDispatchingEvent;

beforeEach(function () {
    $this->channelName = 'testChannel';
    $this->eventBus = Mockery::mock(EventBusInterface::class);
    $this->activePagesRepository = Mockery::mock(ActivePagesRepositoryInterface::class);
    $this->pendingMessagesRepository = Mockery::mock(PendingMessagesRepositoryInterface::class);
    $this->config = Mockery::mock(ConfigInterface::class);
    $this->outgoingCallback = function ($message, $session, $page) {
        return $message;
    };

    $this->dispatcher = new SyncDispatcher(
        $this->eventBus,
        $this->activePagesRepository,
        $this->pendingMessagesRepository
    );

    $this->dispatcher->setOutgoingCallback($this->outgoingCallback);
});

test('outgoing invokes the outgoing callback', function () {
    $message = Mockery::mock(BasicOutgoingMessageInterface::class);
    $session = Mockery::mock(SessionInterface::class);
    $page = Mockery::mock(PageInterface::class);

    $result = $this->dispatcher->outgoing($message, $session, $page);

    expect($result)->toBe($message);
});

test('transition handles page transition correctly', function () {
    $messageContext = Mockery::mock(MessageContextInterface::class);
    $session = Mockery::mock(SessionInterface::class);

    $session->shouldReceive('setCurrentPage')->twice();

    $this->eventBus->shouldReceive('fire')->with(Mockery::type(PageDispatchingEvent::class))->once();
    $this->eventBus->shouldReceive('fire')->with(Mockery::type(PageDispatchedEvent::class))->once();

    $contextPage = Mockery::mock(PageInterface::class);
    $contextPage->shouldReceive('getId')->once()->andReturn('id-1');

    $page = Mockery::mock(PageInterface::class);
    $page->shouldReceive('getId')->once()->andReturn('id-2');
    $page->shouldReceive('getPrevPageId')->twice()->andReturn('id-1');
    $page->shouldReceive('execute')->once()->andReturn(null);

    $this->dispatcher->transition($messageContext, $session, $page, $contextPage);
});

test('incoming processes message and executes page', function () {
    $message = Mockery::mock(BasicIncomingMessageInterface::class);
    $messageContext = Mockery::mock(MessageContextInterface::class);
    $session = Mockery::mock(SessionInterface::class);
    $currentPage = Mockery::mock(PageInterface::class);
    $nextPage = Mockery::mock(PageInterface::class);

    $session->shouldReceive('setCurrentPage')->times(3);

    $message->shouldReceive('getContext')->andReturn($messageContext);

    $this->eventBus->shouldReceive('fire')->with(Mockery::type(PageDispatchingEvent::class))->twice();
    $this->eventBus->shouldReceive('fire')->with(Mockery::type(PageDispatchedEvent::class))->twice();
    $this->eventBus->shouldReceive('fire')->with(Mockery::type(OutgoingMessageSendingEvent::class))->twice();
    $this->eventBus->shouldReceive('fire')->with(Mockery::type(OutgoingMessageSentEvent::class))->twice();
    $this->eventBus->shouldReceive('fire')->with(Mockery::type(IncomingMessageDispatchingEvent::class))->once();

    $currentPage->shouldReceive('setContext')->once();
    $currentPage->shouldReceive('setSession')->once();
    $currentPage->shouldReceive('getId')->once()->andReturn('id-1');
    $currentPage->shouldReceive('setActivePagesRepository')->once()->withArgs([$this->activePagesRepository]);

    $nextPage->shouldReceive('setContext')->once();
    $nextPage->shouldReceive('setSession')->once();
    $nextPage->shouldReceive('getId')->once()->andReturn('id-2');
    $nextPage->shouldReceive('getPrevPageId')->twice()->andReturn('id-1');
    $nextPage->shouldReceive('isDontDisturb')->once()->andReturn(false);
    $nextPage->shouldReceive('setActivePagesRepository')->once()->withArgs([$this->activePagesRepository]);

    $this->pendingMessagesRepository->shouldReceive('isEmpty')->once()->andReturn(true);

    $currentPage->shouldReceive('execute')->once()->withArgs(function ($eventBus, $message, $outgoingCallback) use ($session, $currentPage) {
        $outgoingMessage = Mockery::mock(BasicOutgoingMessageInterface::class);
        $result = $outgoingCallback($outgoingMessage, $session, $currentPage);
        expect($result)->toBe($outgoingMessage);

        return true;
    })->andReturn($nextPage);

    $nextPage->shouldReceive('execute')->once()->withArgs(function ($eventBus, $message, $outgoingCallback) use ($session, $currentPage) {
        $outgoingMessage = Mockery::mock(BasicOutgoingMessageInterface::class);
        $result = $outgoingCallback($outgoingMessage, $session, $currentPage);
        expect($result)->toBe($outgoingMessage);

        return true;
    })->andReturn(null);

    $message->shouldReceive('getPageId')->andReturn('id-1');

    $this->dispatcher->incoming($message, $session, $currentPage);
});

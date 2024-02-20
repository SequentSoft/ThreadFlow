<?php

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\CommonIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\CommonOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Dispatcher\SyncDispatcher;
use SequentSoft\ThreadFlow\Events\Message\OutgoingMessageSendingEvent;
use SequentSoft\ThreadFlow\Events\Message\OutgoingMessageSentEvent;
use SequentSoft\ThreadFlow\Events\Page\PageDispatchedEvent;
use SequentSoft\ThreadFlow\Events\Page\PageDispatchingEvent;

beforeEach(function () {
    $this->channelName = 'testChannel';
    $this->eventBus = Mockery::mock(EventBusInterface::class);
    $this->config = Mockery::mock(ConfigInterface::class);
    $this->outgoingCallback = function ($message, $session, $page) {
        return $message;
    };

    $this->dispatcher = new SyncDispatcher(
        $this->eventBus,
        $this->config,
        $this->outgoingCallback
    );
});

test('outgoing invokes the outgoing callback', function () {
    $message = Mockery::mock(CommonOutgoingMessageInterface::class);
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
    $contextPage->shouldReceive('isTrackingPrev')->once()->andReturn(false);
    $contextPage->shouldReceive('setPrev')->withArgs([null])->once();

    $page = Mockery::mock(PageInterface::class);
    $page->shouldReceive('execute')->once()->andReturn(null);
    $page->shouldReceive('getPrev')->once()->andReturn(null);
    $page->shouldReceive('setPrev')->once();

    $this->dispatcher->transition($messageContext, $session, $page, $contextPage);
});

test('incoming processes message and executes page', function () {
    $message = Mockery::mock(CommonIncomingMessageInterface::class);
    $messageContext = Mockery::mock(MessageContextInterface::class);
    $session = Mockery::mock(SessionInterface::class);
    $currentPage = Mockery::mock(PageInterface::class);
    $nextPage = Mockery::mock(PageInterface::class);

    $session->shouldReceive('getCurrentPage')->once()->andReturn($currentPage);
    $session->shouldReceive('setCurrentPage')->times(3);
    $session->shouldReceive('hasPendingInteractions')->once()->andReturn(false);

    $message->shouldReceive('getContext')->andReturn($messageContext);

    $this->eventBus->shouldReceive('fire')->with(Mockery::type(PageDispatchingEvent::class))->twice();
    $this->eventBus->shouldReceive('fire')->with(Mockery::type(PageDispatchedEvent::class))->twice();
    $this->eventBus->shouldReceive('fire')->with(Mockery::type(OutgoingMessageSendingEvent::class))->twice();
    $this->eventBus->shouldReceive('fire')->with(Mockery::type(OutgoingMessageSentEvent::class))->twice();

    $currentPage->shouldReceive('isTrackingPrev')->once()->andReturn(false);
    $currentPage->shouldReceive('setPrev')->once()->withArgs([null]);

    $nextPage->shouldReceive('setContext')->once();
    $nextPage->shouldReceive('setSession')->once();
    $nextPage->shouldReceive('getPrev')->once()->andReturn(null);
    $nextPage->shouldReceive('setPrev')->once();

    $currentPage->shouldReceive('execute')->once()->withArgs(function ($eventBus, $message, $outgoingCallback) use ($session, $currentPage) {
        $outgoingMessage = Mockery::mock(CommonOutgoingMessageInterface::class);
        $result = $outgoingCallback($outgoingMessage, $session, $currentPage);
        expect($result)->toBe($outgoingMessage);
        return true;
    })->andReturn($nextPage);

    $nextPage->shouldReceive('execute')->once()->withArgs(function ($eventBus, $message, $outgoingCallback) use ($session, $currentPage) {
        $outgoingMessage = Mockery::mock(CommonOutgoingMessageInterface::class);
        $result = $outgoingCallback($outgoingMessage, $session, $currentPage);
        expect($result)->toBe($outgoingMessage);
        return true;
    })->andReturn(null);

    $message->shouldReceive('getPageId')->andReturn('id-1');

    $this->dispatcher->incoming($message, $session);
});

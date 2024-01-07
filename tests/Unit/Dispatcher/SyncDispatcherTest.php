<?php

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\BackgroundPageStatesCollectionInterface;
use SequentSoft\ThreadFlow\Contracts\Session\BreadcrumbsCollectionInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Dispatcher\SyncDispatcher;
use SequentSoft\ThreadFlow\Enums\State\BreadcrumbsType;
use SequentSoft\ThreadFlow\Events\Page\PageDispatchedEvent;
use SequentSoft\ThreadFlow\Events\Page\PageDispatchingEvent;
use SequentSoft\ThreadFlow\Events\Page\PageShowEvent;
use SequentSoft\ThreadFlow\Page\PendingDispatchPage;

beforeEach(function () {
    $this->channelName = 'testChannel';
    $this->eventBus = Mockery::mock(EventBusInterface::class);
    $this->config = Mockery::mock(ConfigInterface::class);
    $this->outgoingCallback = function ($message, $session, $page) {
        return $message;
    };

    $this->dispatcher = new SyncDispatcher(
        $this->channelName,
        $this->eventBus,
        $this->config,
        $this->outgoingCallback
    );
});

test('outgoing invokes the outgoing callback', function () {
    $message = Mockery::mock(OutgoingMessageInterface::class);
    $session = Mockery::mock(SessionInterface::class);
    $page = Mockery::mock(PageInterface::class);

    $result = $this->dispatcher->outgoing($message, $session, $page);

    expect($result)->toBe($message);
});

test('transition handles page transition correctly', function () {
    $messageContext = Mockery::mock(MessageContextInterface::class);
    $session = Mockery::mock(SessionInterface::class);
    $pendingDispatchPage = Mockery::mock(PendingDispatchPage::class);
    $backgroundStates = Mockery::mock(BackgroundPageStatesCollectionInterface::class);
    $breadcrumbsCollection = Mockery::mock(BreadcrumbsCollectionInterface::class);

    $breadcrumbsCollection->shouldReceive('clear')->once();

    $pendingDispatchPage->shouldReceive('getStateId')->andReturn('id-1');
    $pendingDispatchPage->shouldReceive('getPage')->andReturn('Tests\Stubs\EmptyPage');
    $pendingDispatchPage->shouldReceive('getAttributes')->andReturn([]);
    $pendingDispatchPage->shouldReceive('getBreadcrumbsType')->andReturn(BreadcrumbsType::None);

    $backgroundStates->shouldReceive('get')->once()->with('id-1')->andReturn(null);

    $session->shouldReceive('setPageState')->once();
    $session->shouldReceive('getBackgroundPageStates')->andReturn($backgroundStates);
    $session->shouldReceive('getBreadcrumbs')->andReturn($breadcrumbsCollection);
    $backgroundStates->shouldReceive('remove')->once();
    $this->eventBus->shouldReceive('fire')->with(Mockery::type(PageDispatchingEvent::class))->once();
    $this->eventBus->shouldReceive('fire')->with(Mockery::type(PageDispatchedEvent::class))->once();
    $this->eventBus->shouldReceive('fire')->with(Mockery::type(PageShowEvent::class))->once();

    $this->dispatcher->transition($messageContext, $session, $pendingDispatchPage);
});

test('incoming processes message and executes page', function () {
    $message = Mockery::mock(IncomingMessageInterface::class);

    $messageContext = Mockery::mock(MessageContextInterface::class);
    $session = Mockery::mock(SessionInterface::class);
    $backgroundStates = Mockery::mock(BackgroundPageStatesCollectionInterface::class);
    $pageState = Mockery::mock(PageStateInterface::class);

    $message->shouldReceive('getContext')->andReturn($messageContext);

    $backgroundStates->shouldReceive('get')->with('id-1')->andReturn(null);

    $session->shouldReceive('getPageState')->andReturn($pageState);
    $session->shouldReceive('getBackgroundPageStates')->andReturn($backgroundStates);

    $pageState->shouldReceive('getPageClass')->andReturn('Tests\Stubs\EmptyPage');
    $pageState->shouldReceive('getAttributes')->andReturn([]);
    $pageState->shouldReceive('getId')->andReturn('id-1');

    $this->eventBus->shouldReceive('fire')->with(Mockery::type(PageDispatchingEvent::class))->once();
    $this->eventBus->shouldReceive('fire')->with(Mockery::type(PageDispatchedEvent::class))->once();
    $this->eventBus->shouldReceive('fire')->with(Mockery::type(PageShowEvent::class))->once();

    $message->shouldReceive('getStateId')->andReturn('id-1');

    $this->dispatcher->incoming($message, $session);
});

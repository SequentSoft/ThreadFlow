<?php

use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;
use SequentSoft\ThreadFlow\Messages\Incoming\Regular\TextIncomingRegularMessage;
use SequentSoft\ThreadFlow\Messages\Incoming\Service\NewParticipantIncomingServiceMessage;
use SequentSoft\ThreadFlow\Session\PageState;
use SequentSoft\ThreadFlow\Session\Session;

beforeEach(function () {
    $this->makePageWithTextMessage = function (string $class, array $attributes = []) {
        $state = PageState::create($class, $attributes);
        $session = new Session([], $state);
        return new $class(
            $state,
            $session,
            MessageContext::createFromIds('id', 'id'),
            new TextIncomingRegularMessage(
                'id',
                MessageContext::createFromIds('id', 'id'),
                new DateTimeImmutable(),
                'text'
            )
        );
    };

    $this->makePageWithServiceMessage = function (string $class, array $attributes = []) {
        $state = PageState::create($class, $attributes);
        $session = new Session([], $state);
        return new $class(
            $state,
            $session,
            MessageContext::createFromIds('id', 'id'),
            new NewParticipantIncomingServiceMessage(
                'id',
                MessageContext::createFromIds('id', 'id'),
                new DateTimeImmutable(),
            )
        );
    };

    $this->makePageWithoutMessage = function (string $class, array $attributes = []) {
        $state = PageState::create($class, $attributes);
        $session = new Session([], $state);
        return new $class(
            $state,
            $session,
            MessageContext::createFromIds('id', 'id'),
            null
        );
    };
});

it('can be created', function () {
    $page = call_user_func($this->makePageWithTextMessage, \Tests\Stubs\EmptyPage::class);

    expect($page)->toBeInstanceOf(PageInterface::class);
});

it('can be executed with text message', function () {
    $page1 = call_user_func($this->makePageWithTextMessage, \Tests\Stubs\WithAttributePage::class, [
        'foo' => 'bar',
    ]);

    $page2 = call_user_func($this->makePageWithTextMessage, \Tests\Stubs\EmptyHandlersPage::class);

    $spy = Mockery::spy();

    $result = $page1->execute(fn () => $spy->pageExecuteMessageCallback());
    expect($result)->toBeNull();

    $page2->execute(fn () => $spy->pageExecuteMessageCallback());
    $spy->shouldNotHaveReceived('pageExecuteMessageCallback');
});

it('can be executed with service message', function () {
    $page1 = call_user_func($this->makePageWithServiceMessage, \Tests\Stubs\EmptyPage::class);
    $page2 = call_user_func($this->makePageWithServiceMessage, \Tests\Stubs\EmptyHandlersPage::class);

    $spy = Mockery::spy();

    $result = $page1->execute(fn () => $spy->pageExecuteMessageCallback());
    expect($result)->toBeNull();

    $page2->execute(fn () => $spy->pageExecuteMessageCallback());
    $spy->shouldNotHaveReceived('pageExecuteMessageCallback');
});

it('can be executed without message', function () {
    $page1 = call_user_func($this->makePageWithoutMessage, \Tests\Stubs\EmptyPage::class);
    $page2 = call_user_func($this->makePageWithoutMessage, \Tests\Stubs\EmptyHandlersPage::class);

    $spy = Mockery::spy();

    $result = $page1->execute(fn () => $spy->pageExecuteMessageCallback());
    expect($result)->toBeNull();

    $page2->execute(fn () => $spy->pageExecuteMessageCallback());
    $spy->shouldNotHaveReceived('pageExecuteMessageCallback');
});

it('can check is background', function () {
    $page = call_user_func($this->makePageWithTextMessage, \Tests\Stubs\EmptyPage::class);

    expect($page->isBackground())->toBeFalse();
});

it('can return state', function () {
    $page = call_user_func($this->makePageWithTextMessage, \Tests\Stubs\EmptyPage::class);

    expect($page->getState())->toBeInstanceOf(PageStateInterface::class);
});

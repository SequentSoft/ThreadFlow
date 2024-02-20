<?php

use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Events\EventBus;
use SequentSoft\ThreadFlow\Messages\Incoming\Regular\TextIncomingMessage;
use SequentSoft\ThreadFlow\Messages\Incoming\Service\NewParticipantIncomingMessage;
use SequentSoft\ThreadFlow\Session\Session;

beforeEach(function () {
    $this->makePage = function (string $class, array $attributes = []) {
        $session = new Session([], new $class(...$attributes));

        $session->getCurrentPage()
            ->setContext(MessageContext::createFromIds('id', 'id'))
            ->setSession($session)
        ;

        return $session->getCurrentPage();
    };
});

it('can be created', function () {
    $page = call_user_func($this->makePage, \Tests\Stubs\EmptyPage::class);

    expect($page)->toBeInstanceOf(PageInterface::class);
});

it('can be executed with text message', function (string $class, array $attributes) {
    $result = call_user_func($this->makePage, $class, $attributes)->execute(
        new EventBus(),
        TextIncomingMessage::make(text: 'test text'),
        fn () => throw new Exception('This should not be called'),
    );

    // without transition
    expect($result)->toBeNull();
})->with([
    [\Tests\Stubs\WithAttributePage::class, ['foo' => 'bar']],
    [\Tests\Stubs\EmptyHandlersPage::class, []],
]);

it('can be executed with service message', function (string $class, array $attributes) {
    $result = call_user_func($this->makePage, $class, $attributes)->execute(
        new EventBus(),
        NewParticipantIncomingMessage::make(),
        fn () => throw new Exception('This should not be called'),
    );

    // without transition
    expect($result)->toBeNull();
})->with([
    [\Tests\Stubs\EmptyPage::class, []],
    [\Tests\Stubs\EmptyHandlersPage::class, []],
]);

it('can be executed without message', function (string $class, array $attributes) {
    $result = call_user_func($this->makePage, $class, $attributes)->execute(
        new EventBus(),
        null,
        fn () => throw new Exception('This should not be called'),
    );

    // without transition
    expect($result)->toBeNull();
})->with([
    [\Tests\Stubs\EmptyPage::class, []],
    [\Tests\Stubs\EmptyHandlersPage::class, []],
]);

it('can check is background', function () {
    $page = call_user_func($this->makePage, \Tests\Stubs\EmptyPage::class);

    expect($page->isBackground())->toBeFalse();
});

it('can return context', function () {
    /** @var PageInterface $page */
    $page = call_user_func($this->makePage, \Tests\Stubs\EmptyPage::class);

    expect($page->getContext())->toBeInstanceOf(MessageContextInterface::class);
});

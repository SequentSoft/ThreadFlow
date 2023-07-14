<?php

use SequentSoft\ThreadFlow\Page\AbstractPage;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Router\RouterInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

it('constructs without error', function () {
    $messageMock = Mockery::mock(IncomingMessageInterface::class);
    $sessionMock = Mockery::mock(SessionInterface::class);
    $routerMock = Mockery::mock(RouterInterface::class);
    $page = new \Test\PageWithShowText([], $sessionMock, $messageMock, $routerMock);

    expect($page)->toBeInstanceOf(AbstractPage::class);
});

it('sets page event callback', function () {
    $messageMock = Mockery::mock(IncomingMessageInterface::class);
    $sessionMock = Mockery::mock(SessionInterface::class);
    $routerMock = Mockery::mock(RouterInterface::class);
    $page = new \Test\PageWithShowText([], $sessionMock, $messageMock, $routerMock);

    $page->on('testEvent', function ($data) {
        return $data * 2;
    });

    $reflectionClass = new ReflectionClass($page);
    $pageEventsProperty = $reflectionClass->getProperty('pageEvents');
    $pageEventsProperty->setAccessible(true);
    $pageEvents = $pageEventsProperty->getValue($page);

    expect(isset($pageEvents['testEvent']))->toBeTrue();
});

it('emits events', function () {
    $messageMock = Mockery::mock(IncomingMessageInterface::class);
    $sessionMock = Mockery::mock(SessionInterface::class);
    $routerMock = Mockery::mock(RouterInterface::class);
    $page = new \Test\PageWithShowText([], $sessionMock, $messageMock, $routerMock);

    $testData = 2;
    $result = null;

    $page->on('testEvent', function ($data) use (&$result) {
        $result = $data * 2;
    });

    $reflectionClass = new ReflectionClass($page);
    $emitMethod = $reflectionClass->getMethod('emit');
    $emitMethod->setAccessible(true);
    $emitMethod->invokeArgs($page, ['testEvent', $testData]);

    expect($result)->toBe($testData * 2);
});

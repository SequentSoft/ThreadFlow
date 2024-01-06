<?php

use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Dispatcher\SyncDispatcher;
use SequentSoft\ThreadFlow\Messages\Incoming\Regular\TextIncomingRegularMessage;

it('can be created', function () {
    $dispatcher = new SyncDispatcher();

    expect($dispatcher)->toBeInstanceOf(DispatcherInterface::class);
});


it('can dispatch a message', function () {
    $dispatcher = new SyncDispatcher();

    $message = new TextIncomingRegularMessage(
        'id',
        MessageContext::createFromIds('id', 'id'),
        new DateTimeImmutable(),
        'text'
    );

    $spy = Mockery::spy();

    // check was called once
    $dispatcher->incoming('test-channel', $message, function () use ($spy) {
        $spy->dispatched();
    });

    $spy->shouldHaveReceived('dispatched')->once();
});

<?php

use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Dispatcher\SyncIncomingDispatcher;
use SequentSoft\ThreadFlow\Messages\Incoming\Regular\TextIncomingRegularMessage;

it('can be created', function () {
    $dispatcher = new SyncIncomingDispatcher();

    expect($dispatcher)->toBeInstanceOf(DispatcherInterface::class);
});


it('can dispatch a message', function () {
    $dispatcher = new SyncIncomingDispatcher();

    $message = new TextIncomingRegularMessage(
        'id',
        MessageContext::createFromIds('id', 'id'),
        new DateTimeImmutable(),
        'text'
    );

    $spy = Mockery::spy();

    // check was called once
    $dispatcher->dispatch('test-channel', $message, function () use ($spy) {
        $spy->dispatched();
    });

    $spy->shouldHaveReceived('dispatched')->once();
});

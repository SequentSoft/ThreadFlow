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
    $botMock = Mockery::mock(BotInterface::class);

    $dispatcher = new SyncIncomingDispatcher();

    $botMock->shouldReceive('process')->with(
        Mockery::type(TextIncomingRegularMessage::class),
        null
    )->once();

    $dispatcher->dispatch($botMock, new TextIncomingRegularMessage(
        'id',
        MessageContext::createFromIds('id', 'id'),
        new DateTimeImmutable(),
        'text'
    ));
});

<?php

use SequentSoft\ThreadFlow\Dispatcher\SyncIncomingDispatcher;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;

it('dispatches the message to the bot', function () {
    $channelName = 'channelName';
    $incomingMessageMock = $this->createMock(IncomingMessageInterface::class);

    $incomingCallback = function ($message, $session) {
    };
    $outgoingCallback = function ($message, $session) {
    };

    $botMock = $this->createMock(BotInterface::class);
    $botMock->expects($this->once())
        ->method('process')
        ->with($channelName, $incomingMessageMock, $incomingCallback, $outgoingCallback);

    $syncIncomingDispatcher = new SyncIncomingDispatcher($botMock);

    $syncIncomingDispatcher->dispatch($channelName, $incomingMessageMock, $incomingCallback, $outgoingCallback);
});

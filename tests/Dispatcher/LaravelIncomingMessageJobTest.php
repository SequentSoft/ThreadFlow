<?php

use Illuminate\Contracts\Queue\ShouldQueue;
use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Config;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\BotManagerInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Incoming\IncomingChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Incoming\IncomingChannelRegistryInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Outgoing\OutgoingChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Outgoing\OutgoingChannelRegistryInterface;
use SequentSoft\ThreadFlow\Dispatcher\Laravel\IncomingMessageJob;
use SequentSoft\ThreadFlow\Messages\Incoming\Regular\TextIncomingRegularMessage;

it('can be created', function () {
    $job = new IncomingMessageJob('test', new TextIncomingRegularMessage(
        'id',
        MessageContext::createFromIds('id', 'id'),
        new DateTimeImmutable(),
        'text'
    ));

    expect($job)->toBeInstanceOf(ShouldQueue::class);
});

it('can be dispatched', function () {
    $job = new IncomingMessageJob('test', new TextIncomingRegularMessage(
        'id',
        MessageContext::createFromIds('id', 'id'),
        new DateTimeImmutable(),
        'text'
    ));

    $botManagerMock = Mockery::mock(BotManagerInterface::class);

    $botMock = Mockery::mock(BotInterface::class);

    $botMock->shouldReceive('setDispatcher')->once();
    $botMock->shouldReceive('dispatch')->once();

    $botManagerMock->shouldReceive('channel')->andReturn($botMock);

    $job->handle($botManagerMock);
});

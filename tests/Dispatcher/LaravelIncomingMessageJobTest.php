<?php

use Illuminate\Contracts\Queue\ShouldQueue;
use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Config;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
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

    $botMock = Mockery::mock(BotInterface::class);
    $incomingChannelRegistryMock = Mockery::mock(IncomingChannelRegistryInterface::class);
    $outgoingChannelRegistryMock = Mockery::mock(OutgoingChannelRegistryInterface::class);

    $botMock->shouldReceive('getChannelConfig')->andReturn(new Config([
        'test' => [
            'driver' => 'test',
        ],
    ]));

    $incomingChannelRegistryMock->shouldReceive('get')->andReturn(Mockery::mock(IncomingChannelInterface::class));
    $outgoingChannelRegistryMock->shouldReceive('get')->andReturn(Mockery::mock(OutgoingChannelInterface::class));

    $botMock->shouldReceive('process')->with(
        'test',
        Mockery::type(TextIncomingRegularMessage::class),
        Mockery::type(Closure::class),
        Mockery::type(Closure::class)
    )->once();

    $job->handle(
        $botMock,
        $incomingChannelRegistryMock,
        $outgoingChannelRegistryMock
    );
});

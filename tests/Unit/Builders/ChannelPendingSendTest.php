<?php

use SequentSoft\ThreadFlow\Builders\ChannelPendingSend;
use SequentSoft\ThreadFlow\Contracts\Channel\ChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\ParticipantInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\RoomInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PendingDispatchPageInterface;

it('can be instantiated with a channel', function () {
    $channel = \Mockery::mock(ChannelInterface::class);
    $channelPendingSend = new ChannelPendingSend($channel);

    expect($channelPendingSend)->toBeInstanceOf(ChannelPendingSend::class);
});

it('can set a participant', function () {
    $channel = \Mockery::mock(ChannelInterface::class);
    $participant = \Mockery::mock(ParticipantInterface::class);
    $channelPendingSend = new ChannelPendingSend($channel);

    $channelPendingSend->withParticipant($participant);

    $channel->shouldReceive('getName')->once()->andReturn('test');
    $participant->shouldReceive('getId')->once()->andReturn('test-id');

    $channel->shouldReceive('sendMessage')->once()->withArgs(function ($context, $message) {
        expect($message)->toBe('test message')
            ->and($context)->toBeInstanceOf(MessageContextInterface::class)
            ->and($context->getParticipant()->getId())->toBe('test-id');

        return true;
    });

    $channelPendingSend->sendMessage('test message');
});

it('can set a room', function () {
    $channel = \Mockery::mock(ChannelInterface::class);
    $room = \Mockery::mock(RoomInterface::class);
    $channelPendingSend = new ChannelPendingSend($channel);

    $channel->shouldReceive('getName')->once()->andReturn('test');
    $room->shouldReceive('getId')->twice()->andReturn('test-id');

    $channel->shouldReceive('sendMessage')->once()->withArgs(function ($context, $message) {
        expect($message)->toBe('test message')
            ->and($context)->toBeInstanceOf(MessageContextInterface::class)
            ->and($context->getRoom()->getId())->toBe('test-id');

        return true;
    });

    $channelPendingSend->withRoom($room);

    $channelPendingSend->sendMessage('test message');
});

it('can show a page', function () {
    $channel = \Mockery::mock(ChannelInterface::class);
    $page = \Mockery::mock(PendingDispatchPageInterface::class);
    $channelPendingSend = new ChannelPendingSend($channel);

    $channel->shouldReceive('getName')->once()->andReturn('test');
    $channel->shouldReceive('showPage')->once();

    $channelPendingSend->withParticipant('test-id');

    $channelPendingSend->showPage($page, []);
});

it('can send a message', function () {
    $channel = \Mockery::mock(ChannelInterface::class);
    $message = \Mockery::mock(OutgoingMessageInterface::class);
    $channelPendingSend = new ChannelPendingSend($channel);

    $channel->shouldReceive('getName')->once()->andReturn('test');
    $channelPendingSend->withRoom('test-id');

    $channel->shouldReceive('sendMessage')->once()->andReturn($message);

    $result = $channelPendingSend->sendMessage($message);

    expect($result)->toBe($message);
});

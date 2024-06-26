<?php

use SequentSoft\ThreadFlow\Channel\Builders\ChannelPendingSend;
use SequentSoft\ThreadFlow\Contracts\Channel\ChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\ParticipantInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\RoomInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\BasicOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Messages\Outgoing\Regular\TextOutgoingMessage;
use SequentSoft\ThreadFlow\Page\AbstractPage;

beforeEach(function () {
    $this->outgoingMessageMakeCallback = function ($text, $context) {
        return TextOutgoingMessage::make($text)->setContext($context);
    };
});

it('can be instantiated with a channel', function () {
    $channelPendingSend = new ChannelPendingSend(
        Mockery::mock(ChannelInterface::class),
        $this->outgoingMessageMakeCallback,
    );

    expect($channelPendingSend)->toBeInstanceOf(ChannelPendingSend::class);
});

it('can set a participant', function () {
    $channel = Mockery::mock(ChannelInterface::class);
    $participant = Mockery::mock(ParticipantInterface::class);
    $channelPendingSend = new ChannelPendingSend($channel, $this->outgoingMessageMakeCallback);

    $channelPendingSend->withParticipant($participant);

    $channel->shouldReceive('getName')->once()->andReturn('test');
    $participant->shouldReceive('getId')->once()->andReturn('test-id');

    $channel->shouldReceive('dispatchTo')->once()->withArgs(function ($context, $message) {
        expect($message)->toBeInstanceOf(BasicOutgoingMessageInterface::class)
            ->and($context)->toBeInstanceOf(MessageContextInterface::class)
            ->and($context->getParticipant()->getId())->toBe('test-id');

        return true;
    });

    $channelPendingSend->sendMessage('test message');
});

it('can set a room', function () {
    $channel = Mockery::mock(ChannelInterface::class);
    $room = Mockery::mock(RoomInterface::class);
    $channelPendingSend = new ChannelPendingSend($channel, $this->outgoingMessageMakeCallback);

    $channel->shouldReceive('getName')->once()->andReturn('test');
    $room->shouldReceive('getId')->twice()->andReturn('test-id');

    $channel->shouldReceive('dispatchTo')->once()->withArgs(function ($context, $message) {
        expect($message)->toBeInstanceOf(BasicOutgoingMessageInterface::class)
            ->and($context)->toBeInstanceOf(MessageContextInterface::class)
            ->and($context->getRoom()->getId())->toBe('test-id');

        return true;
    });

    $channelPendingSend->withRoom($room);

    $channelPendingSend->sendMessage('test message');
});

it('can show a page', function () {
    $channel = Mockery::mock(ChannelInterface::class);
    $page = Mockery::mock(AbstractPage::class);
    $channelPendingSend = new ChannelPendingSend($channel, $this->outgoingMessageMakeCallback);

    $channel->shouldReceive('getName')->once()->andReturn('test');

    $channel->shouldReceive('dispatchTo')->once()->withArgs(function ($context, $message) {
        expect($message)->toBeInstanceOf(PageInterface::class)
            ->and($context)->toBeInstanceOf(MessageContextInterface::class)
            ->and($context->getRoom()->getId())->toBe('test-id');

        return true;
    });

    $channelPendingSend->withParticipant('test-id');

    $channelPendingSend->showPage($page, []);
});

it('can send a message', function () {
    $channel = Mockery::mock(ChannelInterface::class);
    $message = Mockery::mock(BasicOutgoingMessageInterface::class);
    $channelPendingSend = new ChannelPendingSend($channel, $this->outgoingMessageMakeCallback);

    $channel->shouldReceive('getName')->once()->andReturn('test');
    $channelPendingSend->withRoom('test-id');

    $channel->shouldReceive('dispatchTo')->once()->andReturn($message);

    $result = $channelPendingSend->sendMessage($message);

    expect($result)->toBe($message);
});

it('can force sending a message', function () {
    $channel = Mockery::mock(ChannelInterface::class);
    $message = Mockery::mock(BasicOutgoingMessageInterface::class);
    $channelPendingSend = new ChannelPendingSend($channel, $this->outgoingMessageMakeCallback);

    $channel->shouldReceive('getName')->once()->andReturn('test');
    $channelPendingSend->withRoom('test-id');

    $channel->shouldReceive('dispatchTo')
        ->once()
        ->withArgs(function ($context, $message, $force) {
            expect($force)->toBeTrue();

            return true;
        })
        ->andReturn($message);

    $result = $channelPendingSend->force()->sendMessage($message);

    expect($result)->toBe($message);
});

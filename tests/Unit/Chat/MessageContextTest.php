<?php

use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Chat\Participant;
use SequentSoft\ThreadFlow\Chat\Room;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\ParticipantInterface;

it('can be created', function () {
    $messageContext1 = new MessageContext(
        'test',
        new Participant('participant_id1'),
        new Room('room_id1'),
    );

    expect($messageContext1)->toBeInstanceOf(MessageContextInterface::class);

    $messageContext2 = MessageContext::createFromIds('participant_id2', 'room_id2');

    expect($messageContext2)->toBeInstanceOf(MessageContextInterface::class);
});

it('can return channel name', function () {
    $messageContext = new MessageContext(
        'test',
        new Participant('participant_id1'),
        new Room('room_id1'),
    );

    expect($messageContext->getChannelName())->toBe('test');
});

it('can return participant', function () {
    $messageContext = new MessageContext(
        'test',
        new Participant('participant_id1'),
        new Room('room_id1'),
    );

    expect($messageContext->getParticipant())->toBeInstanceOf(ParticipantInterface::class);
});

it('can return room', function () {
    $messageContext = new MessageContext(
        'test',
        new Participant('participant_id1'),
        new Room('room_id1'),
    );

    expect($messageContext->getRoom())->toBeInstanceOf(Room::class);
});

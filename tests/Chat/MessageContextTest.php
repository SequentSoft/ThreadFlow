<?php

use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Chat\Participant;
use SequentSoft\ThreadFlow\Chat\Room;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\ParticipantInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\IncomingRegularMessageInterface;
use SequentSoft\ThreadFlow\Messages\Incoming\Regular\TextIncomingRegularMessage;

it('can be created', function () {
    $messageContext1 = new MessageContext(
        new Participant('participant_id1'),
        new Room('room_id1'),
    );

    expect($messageContext1)->toBeInstanceOf(MessageContextInterface::class);

    $messageContext2 = MessageContext::createFromIds('participant_id2', 'room_id2');

    expect($messageContext2)->toBeInstanceOf(MessageContextInterface::class);
});

it('can return participant', function () {
    $messageContext = new MessageContext(
        new Participant('participant_id1'),
        new Room('room_id1'),
    );

    expect($messageContext->getParticipant())->toBeInstanceOf(ParticipantInterface::class);
});

it('can return room', function () {
    $messageContext = new MessageContext(
        new Participant('participant_id1'),
        new Room('room_id1'),
    );

    expect($messageContext->getRoom())->toBeInstanceOf(Room::class);
});

it('can be created with forward from', function () {
    $messageContext = new MessageContext(
        new Participant('participant_id1'),
        new Room('room_id1'),
        new Participant('forward_from_id1'),
    );

    expect($messageContext->getForwardFrom())->toBeInstanceOf(ParticipantInterface::class);
});

it('can be created with reply to message', function () {
    $messageContext = new MessageContext(
        new Participant('participant_id1'),
        new Room('room_id1'),
        null,
        new TextIncomingRegularMessage(
            'message_id1',
            MessageContext::createFromIds('participant_id2', 'room_id2'),
            new DateTimeImmutable(),
            'text1',
        )
    );

    expect($messageContext->getReplyToMessage())->toBeInstanceOf(IncomingRegularMessageInterface::class);
});

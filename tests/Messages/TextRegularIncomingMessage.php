<?php

use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Chat\Participant;
use SequentSoft\ThreadFlow\Chat\Room;
use SequentSoft\ThreadFlow\Messages\Incoming\Regular\TextIncomingRegularMessage;

test('TextRegularIncomingMessage properly stores and retrieves properties', function () {
    $participant = new Participant('participant_id');
    $room = new Room('room_id');
    $context = new MessageContext($participant, $room);
    $timestamp = new DateTimeImmutable();
    $rawData = ['key' => 'value'];

    $message = new TextIncomingRegularMessage('message_id', $context, $timestamp, 'Test message');
    $message->setRaw($rawData);

    expect($message->getId())->toBe('message_id');
    expect($message->getContext())->toBe($context);
    expect($message->getTimestamp())->toBe($timestamp);
    expect($message->getText())->toBe('Test message');
    expect($message->getRaw())->toBe($rawData);
});

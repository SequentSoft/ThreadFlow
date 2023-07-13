<?php

use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Chat\Participant;
use SequentSoft\ThreadFlow\Chat\Room;

test('message context can be created and return correct values', function () {
    $participant = new Participant('123');
    $room = new Room('456');
    $context = new MessageContext($participant, $room);

    expect($context->getParticipant())->toBe($participant);
    expect($context->getRoom())->toBe($room);
});

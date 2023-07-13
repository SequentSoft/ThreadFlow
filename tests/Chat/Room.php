<?php

use SequentSoft\ThreadFlow\Chat\Room;

test('room can be created and return correct values', function () {
    $room = new Room('456');
    $room->setName('Test Room')
        ->setType('Test Type')
        ->setDescription('This is a test room')
        ->setParticipantCount(10)
        ->setPhotoUrl('http://example.com/room.jpg');

    expect($room->getId())->toBe('456');
    expect($room->getName())->toBe('Test Room');
    expect($room->getType())->toBe('Test Type');
    expect($room->getDescription())->toBe('This is a test room');
    expect($room->getParticipantCount())->toBe(10);
    expect($room->getPhotoUrl())->toBe('http://example.com/room.jpg');
});

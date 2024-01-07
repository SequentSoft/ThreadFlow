<?php

use SequentSoft\ThreadFlow\Chat\Room;
use SequentSoft\ThreadFlow\Contracts\Chat\RoomInterface;

it('can be created', function () {
    $participant = new Room('room_id1');

    expect($participant)->toBeInstanceOf(RoomInterface::class);
});

it('can return params', function () {
    $participant = new Room('room_id1');
    $participant->setName('name1');
    $participant->setType('type1');
    $participant->setDescription('description1');
    $participant->setParticipantCount(1);
    $participant->setPhotoUrl('photo_url1');

    expect($participant->getId())->toBe('room_id1');
    expect($participant->getName())->toBe('name1');
    expect($participant->getType())->toBe('type1');
    expect($participant->getDescription())->toBe('description1');
    expect($participant->getParticipantCount())->toBe(1);
    expect($participant->getPhotoUrl())->toBe('photo_url1');
});

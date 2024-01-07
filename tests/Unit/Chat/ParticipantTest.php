<?php

use SequentSoft\ThreadFlow\Chat\Participant;
use SequentSoft\ThreadFlow\Contracts\Chat\ParticipantInterface;

it('can be created', function () {
    $participant = new Participant('participant_id1');

    expect($participant)->toBeInstanceOf(ParticipantInterface::class);
});

it('can return params', function () {
    $participant = new Participant('participant_id1');
    $participant->setFirstName('first_name1');
    $participant->setLastName('last_name1');
    $participant->setUsername('username1');
    $participant->setLanguage('language1');
    $participant->setPhotoUrl('photo_url1');

    expect($participant->getId())->toBe('participant_id1');
    expect($participant->getFirstName())->toBe('first_name1');
    expect($participant->getLastName())->toBe('last_name1');
    expect($participant->getUsername())->toBe('username1');
    expect($participant->getLanguage())->toBe('language1');
    expect($participant->getPhotoUrl())->toBe('photo_url1');
});

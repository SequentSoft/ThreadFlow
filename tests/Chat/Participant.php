<?php

use SequentSoft\ThreadFlow\Chat\Participant;

test('participant can be created and return correct values', function () {
    $participant = new Participant('123');
    $participant->setFirstName('John')
        ->setLastName('Doe')
        ->setLanguage('en')
        ->setUsername('johndoe')
        ->setPhotoUrl('http://example.com/photo.jpg');

    expect($participant->getId())->toBe('123');
    expect($participant->getFirstName())->toBe('John');
    expect($participant->getLastName())->toBe('Doe');
    expect($participant->getLanguage())->toBe('en');
    expect($participant->getUsername())->toBe('johndoe');
    expect($participant->getPhotoUrl())->toBe('http://example.com/photo.jpg');
});

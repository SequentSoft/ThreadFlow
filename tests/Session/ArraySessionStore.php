<?php

use SequentSoft\ThreadFlow\Session\ArraySessionStore;
use SequentSoft\ThreadFlow\Session\ArraySessionStoreStorage;
use SequentSoft\ThreadFlow\Session\Session;
use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Chat\Participant;
use SequentSoft\ThreadFlow\Chat\Room;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;

it('loads a new session if none exist', function () {
    $channelName = 'testChannel';
    $config = $this->createMock(ConfigInterface::class);
    $storage = new ArraySessionStoreStorage();

    $sessionStore = new ArraySessionStore($channelName, $config, $storage);

    $participant = new Participant('testParticipant');
    $room = new Room('testRoom');
    $context = new MessageContext($participant, $room);

    $session = $sessionStore->load($context);

    $this->assertInstanceOf(Session::class, $session);
    $this->assertEquals([], $session->getData());
});

it('loads an existing session if one is found', function () {
    $channelName = 'testChannel';
    $config = $this->createMock(ConfigInterface::class);
    $storage = new ArraySessionStoreStorage();

    $sessionStore = new ArraySessionStore($channelName, $config, $storage);

    $participant = new Participant('testParticipant');
    $room = new Room('testRoom');
    $context = new MessageContext($participant, $room);

    // Simulate storing a session in storage
    $sessionData = ['key' => 'value'];
    $sessionStore->save($context, new Session($sessionData, function() {}));

    $session = $sessionStore->load($context);

    $this->assertInstanceOf(Session::class, $session);
    $this->assertEquals($sessionData, $session->getData());
});

it('saves a session', function () {
    $channelName = 'testChannel';
    $config = $this->createMock(ConfigInterface::class);
    $storage = new ArraySessionStoreStorage();

    $sessionStore = new ArraySessionStore($channelName, $config, $storage);

    $participant = new Participant('testParticipant');
    $room = new Room('testRoom');
    $context = new MessageContext($participant, $room);

    $sessionData = ['key' => 'value'];
    $session = new Session($sessionData, function() {});

    $sessionStore->save($context, $session);

    // Check that the session is saved in storage
    $loadedSession = $sessionStore->load($context);
    $this->assertInstanceOf(Session::class, $loadedSession);
    $this->assertEquals($sessionData, $loadedSession->getData());
});

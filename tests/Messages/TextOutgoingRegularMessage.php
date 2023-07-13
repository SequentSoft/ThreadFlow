<?php

use SequentSoft\ThreadFlow\Messages\Outgoing\Regular\TextOutgoingRegularMessage;
use SequentSoft\ThreadFlow\Contracts\Keyboard\KeyboardInterface;
use SequentSoft\ThreadFlow\Keyboard\Keyboard;
use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Chat\Participant;
use SequentSoft\ThreadFlow\Chat\Room;

it('can be instantiated with make static method', function () {
    $text = 'Hello, World!';
    $message = TextOutgoingRegularMessage::make($text);

    $this->assertInstanceOf(TextOutgoingRegularMessage::class, $message);
    $this->assertSame($text, $message->getText());
});

it('can set a KeyboardInterface on a TextOutgoingRegularMessage', function () {
    $text = 'Hello, World!';
    $keyboardMock = $this->createMock(KeyboardInterface::class);

    $message = TextOutgoingRegularMessage::make($text);
    $message->withKeyboard($keyboardMock);

    $this->assertInstanceOf(TextOutgoingRegularMessage::class, $message);
    $this->assertSame($keyboardMock, $message->getKeyboard());
});

it('can set an array keyboard on a TextOutgoingRegularMessage', function () {
    $text = 'Hello, World!';
    $keyboardArray = [['button1' => 'text1'], ['button2' => 'text2']];

    $message = TextOutgoingRegularMessage::make($text);
    $message->withKeyboard($keyboardArray);

    $this->assertInstanceOf(TextOutgoingRegularMessage::class, $message);
    $this->assertInstanceOf(Keyboard::class, $message->getKeyboard());

    // Now that we have the Keyboard object, we can check that it was created correctly from the array
    $rows = $message->getKeyboard()->getRows();
    $this->assertCount(2, $rows);  // We should have 2 rows
    $this->assertEquals('button1', $rows[0]->getButtons()[0]->getCallbackData());
    $this->assertEquals('text1', $rows[0]->getButtons()[0]->getText());
    $this->assertEquals('button2', $rows[1]->getButtons()[0]->getCallbackData());
    $this->assertEquals('text2', $rows[1]->getButtons()[0]->getText());
});

it('can set and get message context', function() {
    $participant = new Participant('test_participant_id');
    $room = new Room('test_room_id');
    $context = new MessageContext($participant, $room);

    $text = 'Hello, World!';
    $message = TextOutgoingRegularMessage::make($text);
    $message->setContext($context);

    $this->assertSame($context, $message->getContext());
});

it('can set and get message id', function() {
    $messageId = 'test_message_id';

    $text = 'Hello, World!';
    $message = TextOutgoingRegularMessage::make($text);
    $message->setId($messageId);

    $this->assertSame($messageId, $message->getId());
});

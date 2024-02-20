# Outgoing Messages

## Overview

Outgoing messages are messages that are sent from the bot to the user.
They implement the `OutgoingMessageInterface` interface. 

The bot can send messages of different types, such as text, images, files, etc.

## Sending Messages to current user

You can send a message using the `reply` method of the `OutgoingMessageInterface` instance or just return it from the page class.

```php
use SequentSoft\ThreadFlow\Messages\Outgoing\Regular\TextOutgoingMessage;

public function show(): void
{
    TextOutgoingMessage::make('Hello, world (1)!')->reply();
    TextOutgoingMessage::make('Hello, world (2)!')->reply();
}

// or just return it from the page class

public function show(): TextOutgoingMessage
{
    return TextOutgoingMessage::make('Hello, world!');
}
```

## Send a message to any bot user

Sometimes you need to send a message to the user outside the conversation flow.
For example, you may want to send a notification to the user when something happens in your application.


You can send a message to the user using the `sendMessage` method:

```php
ThreadFlowBot::channel('telegram')
    ->forParticipant('telegram-user-id-1')
    ->sendMessage('Text message or OutgoingMessageInterface instance');

// or you can pass the OutgoingMessageInterface instance

ThreadFlowBot::channel('telegram')
    ->forParticipant('telegram-user-id-1')
    ->sendMessage(
        ImageOutgoingMessage::make('https://example.com/image.jpg', 'Title')
    );
```
::: tip Note
You can use the `forParticipant` method to send a message to a specific user
or the `forRoom` method to send a message to a specific room.
:::

## Text Messages

Text messages are messages that contain only text. 
You can send a text message using the `TextOutgoingMessage` class.

```php
use SequentSoft\ThreadFlow\Messages\Outgoing\Regular\TextOutgoingMessage;

TextOutgoingMessage::make('Hello, world!');
```

## Image Messages

Image messages are messages that contain an image. 
You can send an image message using the `ImageOutgoingMessage` class.

```php
use SequentSoft\ThreadFlow\Messages\Outgoing\Regular\ImageOutgoingMessage;

ImageOutgoingMessage::make('https://example.com/image.jpg', 'Title');
```

## File Messages

File messages are messages that contain a file.

You can send a file message using the `FileOutgoingMessage` class.

```php
use SequentSoft\ThreadFlow\Messages\Outgoing\Regular\FileOutgoingMessage;

FileOutgoingMessage::makeFromUrl('https://example.com/file.pdf', 'Title');

// or using path

FileOutgoingMessage::makeFromPath('/path/to/file.pdf', 'Title');
```

## Forward Messages

Forward messages are messages that contain a reference to another message.
You can send a forward message using the `ForwardOutgoingMessage` class.

```php

use SequentSoft\ThreadFlow\Messages\Outgoing\Regular\ForwardOutgoingMessage;

ForwardOutgoingMessage::make($incomingMessage);
```


## Typing indicator

Typing indicator is a message that indicates that the bot is typing. 
You can send a typing indicator using the `TypingOutgoingServiceMessage` class.

```php
use SequentSoft\ThreadFlow\Messages\Outgoing\Service\TypingOutgoingServiceMessage;
use SequentSoft\ThreadFlow\Enums\Messages\TypingType;

TypingOutgoingServiceMessage::make(TypingType::TYPING);

// or just use the helper method in the page class

$this->showTyping();
```

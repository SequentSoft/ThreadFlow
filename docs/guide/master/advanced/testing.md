# Testing

## Introduction

Testing is an important part of the development process.
It helps to ensure that the code works as expected and that it continues to work as expected as the codebase evolves.
This guide will cover how to test your ThreadFlow code.

## Prevent sending messages

When you are testing your bot, you may want to prevent the bot from sending messages.
You can use the `fake` method to prevent the bot from sending messages.

```php
use SequentSoft\ThreadFlow\Laravel\Facades\ThreadFlowBot;

ThreadFlowBot::fake();
```

It will replace bot dispatcher with a fake dispatcher that will not send messages.

## Fake incoming messages

You can fake incoming messages using the `test` and `input` method.

```php
ThreadFlowBot::channel('telegram')
    ->test()
    ->input('Hello');
```

It will create a fake incoming message with the text `Hello`.
Also, you can pass incoming message data as object.

```php
use SequentSoft\ThreadFlow\Messages\Incoming\Regular\LocationIncomingMessage;

ThreadFlowBot::channel('telegram')
    ->test()
    ->input(fn (MessageContextInterface $context) => 
        LocationIncomingMessage::make(
            latitude: 49.8419388,
            longitude: 24.0315747,
            context: $context
        )
    );
```
Use closure to create a message with fake message context.

## Clicking buttons

You can fake clicking buttons using the `click` method.

```php
ThreadFlowBot::channel('telegram')
    ->test()
    ->click('yes');
```

## Contact message

You can fake sending contact message using the `contact` method.

```php
ThreadFlowBot::channel('telegram')
    ->test()
    ->contact('+380123456789', 'John', 'Doe', 'user-id-1');
```

## Location message

You can fake sending location message using the `location` method.

```php
ThreadFlowBot::channel('telegram')
    ->test()
    ->location(49.8419388, 24.0315747);
```

### Use initial state

You can use the `withPage` method to set the initial state of the bot.

```php
ThreadFlowBot::channel('telegram')
    ->test()
    ->withPage(new IndexPage()) // object or class name IndexPage::class
    ->click('yes')
    ->assertState(IndexPage::class)
````

## Asserting

After you have faked incoming messages, you can assert that the bot sent a message using the `assert` methods.

```php
ThreadFlowBot::channel('telegram')
    ->test()
    ->click('yes')
    ->assertState(IndexPage::class)
    ->assertOutgoingMessagesCount(1)
    ->assertOutgoingMessageTextContains('Hello, world!');
```

## Asserting methods

### `assertState(string $pageClass, ?string $method = null, ?array $attributes = null, ?int $index = null): static;`

Assert that the bot is in the specified state. You can pass the method name and attributes to check the state of the page.
If you want to check the state of the page in the chain, you can pass the index of the page. By default, it will check the last page in the chain.

```php
ThreadFlowBot::channel('telegram')
    ->test()
    ->input('Hello')
    ->assertState(SecondPage::class, 'show', ['id' => 1]);
```

### `assertOutgoingMessageText(string $text, ?int $index = null): static;`

Assert that the bot sent a message with the specified text.

### `assertOutgoingMessageTextContains(string $text, ?int $index = null): static;`

Assert that the bot sent a message that contains the specified text.

### `assertStatesChain(array $states): static;`

Assert that the bot is in the specified states chain.

```php
ThreadFlowBot::channel('telegram')
    ->test()
    ->input('Hello')
    ->assertStatesChain([
        [IndexPage::class, 'answer'],
        [SecondPage::class, 'show'],
    ]);
```

### `assertOutgoingMessagesCount(int $count): static;`

Assert that the bot sent the specified number of messages.

### `assertOutgoingMessage(Closure $callback, ?int $index = null): static;`

Assert that the bot sent a message that matches the given condition.

```php

ThreadFlowBot::channel('telegram')
    ->test()
    ->input('Hello')
    ->assertOutgoingMessage(fn (OutgoingMessageInterface $message) => 
        $message->getText() === 'Hello, world!'
    );
```

### `assertDispatchedPagesCount(int $count): static;`

Assert that the bot dispatched the specified number of pages.

### `assertDispatchedPage(Closure $callback, ?int $index = null): static;`

Assert that the bot dispatched a page that matches the given condition.

```php
ThreadFlowBot::channel('telegram')
    ->test()
    ->input('Hello')
    ->assertDispatchedPage(fn (PageInterface $page) => 
        $page instanceof IndexPage
    );
```

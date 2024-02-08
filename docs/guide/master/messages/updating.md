# Updating Messages

Updating messages is a way to update the content of a message that has been sent.

For example, you can update the text of a message, or the keyboard of a message with a keyboard.
It is useful with inline keyboards, when you want to change the keyboard after the user has clicked on a button.

## Update text

You can update the text of outgoing messages using the `update` method if the message was sent using the `reply` method:

```php
protected ?OutgoingMessageInterface $counterMessage = null;

protected int $counter = 0;

public function show()
{
    $this->counterMessage = TextOutgoingMessage::make('Hello, world! First time')->reply();
}

public function answer(IncomingRegularMessageInterface $message)
{
    $this->counter++;
    $this->counterMessage->setText(
        "Hello, world! You have clicked {$this->counter} times"
    );
    $this->counterMessage->update();
}

```

## Update keyboard

You can update the keyboard of outgoing messages using the `update` method if the message was sent using the `reply` method:

```php
protected ?OutgoingMessageInterface $message = null;

public function show()
{
    $this->message = TextOutgoingMessage::make('Hello!')->withKeyboard(
        Keyboard::make()->inline()->row([
            Button::text('Click me', 'click'),
        ])
    )->reply();
}

public function answer(IncomingRegularMessageInterface $message)
{
    $this->message->withKeyboard(
        Keyboard::make()->inline()->row([
            Button::text('Clicked', 'clicked'),
        ])
    )->update();
}

```

## Add reaction

You can add a reaction to the message using the `addReaction` method:

```php
use SequentSoft\ThreadFlowTelegram\Enums\Messages\EmojiReaction;

public function answer(IncomingRegularMessageInterface $message)
{
    $message->sendReaction(EmojiReaction::THUMBS_UP);
}

```

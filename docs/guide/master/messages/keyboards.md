# Keyboards

Keyboards are a way to provide a user with a set of predefined options to choose from.
They are a great way to make your bot more interactive and user-friendly.

You can use keyboards only with the outgoing messages.

## Usage

You can create a keyboard from an array of buttons:

```php
TextOutgoingMessage::make('Hello, world!', [
    ['yes' => 'Yes, please'],
    ['no' => 'No, thanks'],
    ['cancel' => 'Cancel'],
])->reply();
```
After sending this message, the user will see a keyboard with three buttons in one column:
 - Yes, please
 - No, thanks
 - Cancel

And when the user clicks on one of the buttons, the bot will receive a message with the text of the button.

### Example

```php
public function show()
{
    TextOutgoingMessage::make('Continue?', [
        ['yes' => 'Yes, please'],
        ['no' => 'No, thanks'],
    ])->reply();
}

public function answer(IncomingMessageInterface $message)
{
    if ($message->isClicked('yes')) {
        // User clicked on "Yes, please"
    }
    
    if ($message->isClicked('no')) {
        // User clicked on "No, thanks"
    }
}
```

## How add a keyboard

You can add a keyboard to any outgoing message as array of buttons or as a `Keyboard` object.

### As message constructor argument

Latest argument of the `make` method is a keyboard object or array.

```php
return TextOutgoingMessage::make('Hello, world!', $keyboard);
```
or using `withKeyboard` method:

```php
return TextOutgoingMessage::make('Hello, world!')
    ->withKeyboard($keyboard, 'placeholder text');
```

## Button object

You can use a `Button` object to create a button and use additional methods to customize it.
Use the `Button` class to create a button.

`SequentSoft\ThreadFlow\Keyboard\Button`

```php
return TextOutgoingMessage::make('Hello, world!', [
    Button::text('Yes, please', 'yes'),
    Button::text('No, thanks', 'no'),
    Button::text('Cancel', 'cancel'),
]);
```

Also, you can use the `Button` class to create a button with a different type:

### Simple text button

User can click on the button to send the text of the button to the bot.

```php
Button::text('Yes, please', 'yes'),
```

### Contact button

User can click on the button to send the contact information to the bot.

```php
Button::contact('Send contact'),
```

### Location button

User can click on the button to send the location to the bot.

```php
Button::location('Send location'),
```

## Keyboard object

Instead of an array, you can use a `Keyboard` object to create a keyboard and use additional methods to customize it.

```php
use SequentSoft\ThreadFlow\Keyboard\Keyboard;

// one column keyboard
$keyboard = Keyboard::createFromArray([
    ['yes' => 'Yes, please'],
    ['no' => 'No, thanks'],
    ['cancel' => 'Cancel'],
]);

// the same one column keyboard
$keyboard = Keyboard::createFromArray([
    'yes' => 'Yes, please',
    'no' => 'No, thanks',
    'cancel' => 'Cancel',
]);

// two column keyboard
$keyboard = Keyboard::createFromArray([
    ['yes' => 'Yes, please', 'no' => 'No, thanks'],
    ['cancel' => 'Cancel'],
]);
```

### `static createFromArray(array $buttons): Keyboard`

Creates a keyboard from an array of buttons. 

### `oneTimeKeyboard(): Keyboard`

Sets the keyboard to be shown only once.

### `resizable(bool $resizable = true): Keyboard`

Sets the keyboard to be resizable. By default, the keyboard is resizable.

### `notResizable(): Keyboard`

Sets the keyboard to be not resizable.

### `placeholder(string $placeholder): Keyboard`

Sets the keyboard to be inline. By default, the keyboard is not inline.

### `row(array $buttons): Keyboard`

Adds a row of buttons to the keyboard.

### `column(array $buttons): Keyboard`

## Inline keyboard

You can use an inline keyboard to create a keyboard with buttons that are shown inline with the message.
To use an inline keyboard, you can use the `inline` method of the `Keyboard` object.

```php
use SequentSoft\ThreadFlow\Keyboard\Keyboard;

$keyboard = Keyboard::make()->inline()->row(
    Button::text('Yes, please', 'yes'),
    Button::text('No, thanks', 'no'),
    Button::text('Cancel', 'cancel'),
);
```

# Incoming Messages

## Overview

Incoming messages are messages that are sent to the bot by users.
The bot can receive messages of different types, such as text, images, files, etc.

The bot can also receive service messages, such as a message about a new participant in the chat or a message about the start of the bot.

When the bot receives a message, you can process it and send a response.

## Incoming Regular Messages

Incoming regular messages are messages that are sent to the bot by users.
All this messages implements this interface:
```php
SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\IncomingRegularMessageInterface
```

## Common Methods

All incoming regular messages have the following common methods:

### `getText(): string`

Returns the text of the message. If the message is not a text message, it returns an empty string.

### `isText(?string $text = null): bool`

Checks if the message is a text message. If the `$text` parameter is passed, it checks if the message text is equal to the passed text.

### `isTextAndContains(string $text): bool`

Checks if the message is a text message and if the message text contains the passed text.

### `isTextAndMatch(string $expression): bool`

Checks if the message is a text message and if the message text matches the passed regular expression.

### `isLocation(): bool`

Checks if the message is a location message.

### `isSticker(): bool`

Checks if the message is a sticker message.

### `isVideo(): bool`

Checks if the message is a video message.

### `isImage(): bool`

Checks if the message is an image message.

### `isAudio(): bool`

Checks if the message is an audio message.

### `isContact(): bool`

Checks if the message is a contact message.

### `isFile(): bool`

Checks if the message is a file message.

::: tip TIP
You can use these methods to check the type of the incoming message and process it accordingly.

But better way to detect the type of the message is to use `instanceof` operator and check the message type directly.
It will help IDE to provide you with the correct methods for the message type.

```php
public function answer(IncomingRegularMessageInterface $message)
{
    if ($message instanceof LocationIncomingRegularMessage) {
        $position = [
            'latitude' => $message->getLatitude(),
            'longitude' => $message->getLongitude(),
        ];
    }
}
```
:::

## Text Message

Text message is a message that contains only text. It implements this interface:
```php
SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\TextIncomingRegularMessageInterface
```

No additional methods for this message type.

## Contact Message

Contact message is a message that contains contact information. It implements this interface:
```php
SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\ContactIncomingRegularMessageInterface
```

### `getPhoneNumber(): string`

Returns the phone number of the contact. For example, `+1234567890`.

### `getFirstName(): string`

Returns the first name of the contact. For example, `John`.

### `getLastName(): string`

Returns the last name of the contact. For example, `Doe`.

### `getUserId(): string`

Returns the user ID of the contact. For example, `1234567890`.

## Location Message

Location message is a message that contains location information. It implements this interface:
```php
SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\LocationIncomingRegularMessageInterface
```

### `getLatitude(): float`

Returns the latitude of the location. For example, `55.7558`.

### `getLongitude(): float`

Returns the longitude of the location. For example, `37.6176`.

## Image Message

Image message is a message that contains an image. It implements this interface:
```php
SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\ImageIncomingRegularMessageInterface
```

### `getUrl(): string`

Returns the URL of the image. For example, `https://example.com/image.jpg`.

### `getName(): string`

Returns the name of the image. For example, `image.jpg`.


## File Message

File message is a message that contains a file. It implements this interface:
```php
SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\FileIncomingRegularMessageInterface
```

### `getUrl(): string`

Returns the URL of the file. For example, `https://example.com/file.pdf`.

### `getName(): string`

Returns the name of the file. For example, `file.pdf`.

## Audio Message

Audio message is a message that contains an audio file. It implements this interface:

```php
SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\AudioIncomingRegularMessageInterface
```

### `getUrl(): string`

Returns the URL of the audio file. For example, `https://example.com/audio.mp3`.

### `getName(): string`

Returns the name of the audio file. For example, `audio.mp3`.

## Video Message

Video message is a message that contains a video file. It implements this interface:
```php
SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\VideoIncomingRegularMessageInterface
```

### `getUrl(): string`

Returns the URL of the video file. For example, `https://example.com/video.mp4`.

### `getName(): string`

Returns the name of the video file. For example, `video.mp4`.

## Sticker Message

Sticker message is a message that contains a sticker. It implements this interface:
```php
SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\StickerIncomingRegularMessageInterface
```

### `getStickerId(): string`

Returns the ID of the sticker. For example, `1234567890`.

### `getEmoji(): string`

Returns the emoji of the sticker. For example, `😊`.

### `getName(): string`

Returns the name of the sticker. For example, `smiling-face`.

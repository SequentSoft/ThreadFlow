
<p align="center">
<a href="https://github.com/SequentSoft/ThreadFlow/actions"><img src="https://github.com/SequentSoft/ThreadFlow/actions/workflows/tests.yml/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/sequentsoft/threadflow"><img src="https://img.shields.io/packagist/dt/sequentsoft/threadflow" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/sequentsoft/threadflow"><img src="https://img.shields.io/packagist/v/sequentsoft/threadflow" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/sequentsoft/threadflow"><img src="https://img.shields.io/packagist/l/sequentsoft/threadflow" alt="License"></a>
</p>


# ThreadFlow

ThreadFlow is a flexible PHP library designed to simplify the process of developing chatbots, especially for the Laravel framework. It allows developers to easily structure chatbot logic with the concept of "pages". Depending on the driver, ThreadFlow can be used to create bots for different messaging platforms such as Telegram, Viber, and more.

## Installation

Install the library via Composer:

```bash
composer require sequentsoft/threadflow
```

And telegram driver:
```bash
composer require sequentsoft/threadflow-telegram
```

## Configuration

After installation, you need to publish the ThreadFlow configuration file:

```bash
php artisan vendor:publish --provider="SequentSoft\ThreadFlow\LaravelServiceProvider"
```

This command will create a `thread-flow.php` configuration file in your `config` directory. Here is an example of the published configuration file:

```php
return [
    'channels' => [
        'cli' => [
            'driver' => 'cli',
            'session' => 'array',
            'dispatcher' => 'sync',
            'entry' => \App\ThreadFlow\Pages\IndexPage::class,
        ],
        'test' => [
            'driver' => 'test',
            'session' => 'array',
            'dispatcher' => 'sync',
            'entry' => \App\ThreadFlow\Pages\IndexPage::class,
        ],
        'telegram' => [
            'driver' => 'telegram',
            'session' => 'cache', // cache or array
            'dispatcher' => env('THREAD_FLOW_DISPATCHER', 'sync'), // sync or queue
            'entry' => \App\ThreadFlow\Pages\IndexPage::class,
            'api_token' => env('THREAD_FLOW_TELEGRAM_API_TOKEN', null),
            'webhook_url' => env('THREAD_FLOW_TELEGRAM_WEBHOOK_URL', null),
            'timeout' => 30,
            'limit' => 100,
        ],
    ],
];
```

## Usage

You can generate a new page using the artisan command:

```bash
php artisan thread-flow:page PageName
```

After that, you can manually add logic to the created page classes. Below are examples of possible page classes:

```php
class YourPage extends AbstractPage
{
    /**
     * The show method is the first method that gets called when a page is opened. 
     * Typically, you would send an initial message to the user here (like a welcome message 
     * or an overview of the page's functions). You can use the `reply` method 
     * to send a message to the user.
     */
    protected function show()
    {
        // Send initial message
    }

    /**
     * The handleMessage method gets called when a user sends a message while they're on this page.
     * Here you can handle user input. The $message parameter contains the user's message. 
     * You can use the `reply` method to send a message back to the user.
     *
     * @param IncomingRegularMessageInterface $message - The user's message.
     * 
     * @return void
     */
    protected function handleMessage(IncomingRegularMessageInterface $message)
    {
        // Handle user input
    }
}
```

## Handling Different Types of Incoming Messages

ThreadFlow supports various types of incoming messages, making it a versatile tool for different kinds of chatbot interactions. The different types of incoming messages are implemented through various interfaces, each corresponding to a specific type of content.

Below is a table that outlines each type of incoming message that ThreadFlow supports:

| Type of Message | Corresponding Interface                   |
|-----------------|-------------------------------------------|
| Audio           | `AudioIncomingRegularMessageInterface`    |
| Contact         | `ContactIncomingRegularMessageInterface`  |
| File            | `FileIncomingRegularMessageInterface`     |
| Image           | `ImageIncomingRegularMessageInterface`    |
| Location        | `LocationIncomingRegularMessageInterface` |
| Sticker         | `StickerIncomingRegularMessageInterface`  |
| Text            | `TextIncomingRegularMessageInterface`     |
| Video           | `VideoIncomingRegularMessageInterface`    |

Each of these interfaces in the namespace: `SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular`.

In addition, the `IncomingRegularMessageInterface` interface has methods to check the type of incoming message:

```php
public function isText(?string $text = null): bool;
public function isLocation(): bool;
public function isSticker(): bool;
public function isVideo(): bool;
public function isImage(): bool;
public function isAudio(): bool;
public function isContact(): bool;
public function isFile(): bool;
```

To handle different types of incoming messages, you should define the `handleMessage` method in your page class. In this method, you can use instanceof operator to determine the type of the incoming message:

```php
protected function handleMessage(IncomingRegularMessageInterface $message)
{
    if ($message->isText('Hello')) {
        // handle text message
        return;
    }
    
    if ($message instanceof ImageIncomingRegularMessageInterface) {
        // $message->getUrl()
        // handle image message
        return;
    }
    
    if ($message->isLocation()) {
        // $message->getLatitude()
        // $message->getLongitude()
        // handle location message
        return;
    }
    // ... and so on for other types of messages
}
```

In this method, you can define your own logic to handle each type of message, depending on the requirements of your chatbot. Remember, this is just a basic illustration of handling different types of messages, and in actual implementation, you would replace "// handle text message", "// handle image message", and "// handle video message" with your own code.

## Changing Pages

There are two ways to switch from one page to another: by using the next() and back() methods.

The `next()` method transfers the user to a new page. It takes two arguments: the first one is the class name of the page you want to navigate to, and the second one is an associative array of arguments you want to pass to the new page. 
```php
return $this->next(OtherPage::class, ['argument1' => 'value1', 'argument2' => 'value2']);
```

If you call the `next()` method with the `withBreadcrumbs()` method before it, the current page will be added to the breadcrumbs. This means that the user can go back to this page using the `back()` method on the new page.
```php
return $this->next(OtherPage::class)->withBreadcrumbs();
```

The `back()` method returns the user to the previous page. You can specify a fallback page class name as an argument that will be used if the breadcrumbs are empty.
```php
return $this->back(FallbackPage::class);
```

## Outputting Messages
ThreadFlow provides method to send messages back to the user. The `reply()` method sends a message to the user. It takes an argument that is an instance of a class that implements OutgoingRegularMessageInterface.

```php
$this->reply(
    new TextOutgoingRegularMessage('Hello, user!')
);
```
or
```php
$this->reply(
    TextOutgoingRegularMessage::make('Hello, user!')
        ->withKeyboard([
            ['next' => 'Go to the next page'],
        ])
);
```

## Working with Keyboards
ThreadFlow allows you to create interactive keyboards for your chatbot conversations. Keyboards make it easy for users to choose from a set of options, rather than having to type out their responses. You can customize keyboards to suit the needs of your specific bot.

In ThreadFlow, keyboards are associated with outgoing messages. Keyboards can be defined by passing an array of button options or an instance of `Keyboard::createFromArray` to the outgoing message constructor. Each button option is a key-value pair, where the key is the callback data (i.e., the data you'll receive when the button is clicked), and the value is the text displayed on the button.

For instance:
```php
$this->reply(new TextOutgoingRegularMessage('Choose an option', [
    ['option1' => 'Option 1'],
    ['option2' => Button::text('Option 2')],
    [Button::text('Option 3', 'option3')],
]));
```

This would prompt the bot to send a message, "Choose an option", with a keyboard containing three buttons: "Option 1", "Option 2", and "Option 3". When a user clicks one of these buttons, the bot will receive a message with the corresponding callback data (i.e., "option1", "option2", or "option3").

To handle the input accordingly, you can use the handleMessage method in your page class:

```php
protected function handleMessage(IncomingRegularMessageInterface $message)
{
    if ($message->isText('option1')) {
        // handle option 1
    } elseif ($message->isText('option2')) {
        // handle option 2
    } elseif ($message->isText('option3')) {
        // handle option 3
    }
}
```

In the given example, if the user selects "Option 1", the bot will receive a message with the text "option1", and the code in the "handle option 1" block will be executed. The same principle applies for "Option 2" and "Option 3".

For more advanced keyboard configurations, `Keyboard::createFromArray` can be used:

```php
$keyboard = Keyboard::createFromArray([
    ['option1' => 'Option 1', 'option2' => 'Option 2'],
    [Button::contact('Share your contact'), Button::location('Share your location')],
]);

$this->reply(new TextOutgoingRegularMessage('Choose an option', $keyboard));
```

In this case, the keyboard will have two rows: the first with text buttons 'Option 1' and 'Option 2', and the second row with 'Share your contact' and 'Share your location' buttons, which prompt the user to share their contact and location information respectively. Button customization enables you to tailor user interactions and provide a richer experience.

## Run

You can then run the bot with a command that corresponds to your desired driver, for example:
```bash
php artisan thread-flow:telegram:long-polling telegram
```
where `telegram` is a channel name

Or you can use cli mode:
```bash
php artisan thread-flow:cli
```

## Code Examples

Please note that the following examples are only intended to illustrate some of the ways that the ThreadFlow library can be used. They are not intended to be prescriptive or to represent the only "correct" way to use the library. Developers should adapt these examples to suit their own needs and the specific requirements of their projects.

### Index Page
The index page is the starting point for your bot. In this example, the `show` method replies with a greeting message and some options for the user to choose from. The `handleMessage` method then handles the user's input:

```php
class IndexPage extends AbstractPage
{
    protected function show()
    {
        $this->reply(new TextOutgoingRegularMessage('This is IndexPage', [
            ['login' => 'Login'],
            ['about' => 'About bot'],
            ['products' => 'View products'],
        ]));
    }

    protected function handleMessage(IncomingRegularMessageInterface $message)
    {
        if ($message->isText('login')) {
            return $this->next(LoginPage::class)->withBreadcrumbs();
        }

         if ($message->isText('products')) {
            return $this->next(ProductsPage::class);
        }

        if ($message->isText('about')) {
            $this->reply(new TextOutgoingRegularMessage('This is my first bot'));
            return null;
        }

        $this->reply(new TextOutgoingRegularMessage('You answered [' . $message->getText() . ']. Please choose option', [
            ['login' => 'Login'],
            ['about' => 'About bot'],
            ['products' => 'View products'],
        ]));
    }
}
```

### Products Page

In this page, the show method displays a list of products and an option to go back. The handleMessage method then handles the user's choice:

```php
class ProductsPage extends AbstractPage
{
    protected function productsList()
    {
        return [
            'Product 1',
            'Product 2',
            'Product 3',
            'Product 4',
        ];
    }

    protected function show()
    {
        $this->reply(new TextOutgoingRegularMessage(
            "This is ProductsPage. Products list:\n" . implode("\n", $this->productsList()),
            array_merge(
                array_map(fn ($product) => [$product => $product], $this->productsList()),
                [
                    ['back' => 'Go back'],
                ]
            )
        ));
    }

    protected function handleMessage(IncomingRegularMessageInterface $message)
    {
        if ($message->isText('back')) {
           return $this->next(IndexPage::class);
        }

        $this->reply(new TextOutgoingRegularMessage(
            "Please choose option. Products list:\n" . implode("\n", $this->productsList()),
            array_merge(
                array_map(fn ($product) => [$product => $product], $this->productsList()),
                [
                    ['back' => 'Go back'],
                ]
            )
        ));
    }
}
```

### Login Pages

The LoginPage and EnterPasswordPage work together to handle the user login process. The user is asked to enter their login details on the LoginPage, and if the login is valid, the user is taken to the EnterPasswordPage.

```php
class LoginPage extends AbstractPage
{
    protected function show()
    {
        $this->reply(new TextOutgoingRegularMessage('Enter your login', [
            ['back' => 'Back'],
        ]));
    }

    protected function handleMessage(IncomingRegularMessageInterface $message)
    {
        if ($message->isText('back')) {
            return $this->back();
        }

        $login = $message->getText();

        if (validateLogin($login)) {
            return $this->next(EnterPasswordPage::class, ['login' => $login])
                ->withBreadcrumbs();
        }

        $this->reply(
            new TextOutgoingRegularMessage('Login is not valid. Please try again', [
                ['back' => 'Back'],
            ])
        );
    }
}
```

EnterPasswordPage:

```php
class EnterPasswordPage extends AbstractPage
{
    protected string $login;

    protected function show()
    {
        $this->reply(TextOutgoingRegularMessage::make('Enter your password', [
            ['back' => 'Back'],
        ]));
    }

    protected function handleMessage(IncomingRegularMessageInterface $message)
    {
        if ($message->isText('back')) {
            return $this->back(LoginPage::class);
        }

        $password = $message->getText();

        if (validatePassword($this->login, $password)) {
            return $this->next(IndexPage::class);
        }

        $this->reply(
            TextOutgoingRegularMessage::make('Password is not valid. Please try again', [
                ['back' => 'Back'],
            ])
        );
    }
}
```

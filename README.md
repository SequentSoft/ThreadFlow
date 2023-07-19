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

ThreadFlow provides interfaces for different types of incoming messages that you can utilize to create versatile and interactive experiences for your users. Each type of incoming message has a corresponding interface, which ensures you're provided with all necessary data of that message type. All interfaces in namespace `SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular`. Here's a breakdown of what each interface does:

1. `TextIncomingRegularMessageInterface`:This interface is used for handling text messages from the user. It provides the `getText()` method which returns the text of the message.

2. `AudioIncomingRegularMessageInterface`: This interface is used for handling audio messages. It provides two methods: `getAudioUrl()` which returns the URL of the audio file, and `getCaption()` which returns the caption of the audio file (if there is one).

3. `ImageIncomingRegularMessageInterface`: This interface is used for handling image messages. It provides two methods: `getImageUrl()` which returns the URL of the image, and `getCaption()` which returns the caption of the image (if there is one).

4. `LocationIncomingRegularMessageInterface`: This interface is used for handling location messages. It provides two methods: `getLatitude()` and `getLongitude()` which return the latitude and longitude of the location respectively.

5. `StickerIncomingRegularMessageInterface`: This interface is used for handling sticker messages. It provides the `getStickerId()` method which returns the ID of the sticker.

6. `VideoIncomingRegularMessageInterface`: This interface is used for handling video messages. It provides two methods: `getVideoUrl()` which returns the URL of the video, and `getCaption()` which returns the caption of the video (if there is one).

You can utilize these interfaces in your pages to process incoming messages according to their types. Here's an example:

```php
protected function handleMessage(IncomingRegularMessageInterface $message)
{
    if ($message instanceof TextIncomingRegularMessageInterface) {
        // process text message
    } elseif ($message instanceof ImageIncomingRegularMessageInterface) {
        // process image message
    }
    // continue with other types...
}
```

### Code Examples

Please note that the following examples are only intended to illustrate some of the ways that the ThreadFlow library can be used. They are not intended to be prescriptive or to represent the only "correct" way to use the library. Developers should adapt these examples to suit their own needs and the specific requirements of their projects.

#### Index Page
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

#### Products Page

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

#### Login Pages

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
        $login = $this->getAttribute('login');

        if (validatePassword($login, $password)) {
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

### Run

You can then run the bot with a command that corresponds to your desired driver, for example:
```bash
php artisan thread-flow:telegram:long-polling telegram
# where `telegram` is a channel name
```

Or you can use cli mode:
```bash
php artisan thread-flow:cli
```

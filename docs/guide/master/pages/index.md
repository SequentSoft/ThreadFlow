# Pages

A page is a php class that describes the logic of the bot's behavior.

Each page has a `show` method that describes the logic for sending the initial message to the user when the page is opened.
Also, each page has a `handle` method that describes the logic for handling user input.

If you need to go to another page when processing a message, use the `next` method,
which takes the name of the page to go to and attributes.

::: info INFO
By default, all pages are located in the `app/ThreadFlow/Pages` directory.
:::

## Creating a page

You can create page class manually or generate it using the artisan command:

```sh [artisan]
$ php artisan threadflow:page IndexPage
```

It will create a new page class `app/ThreadFlow/Pages/IndexPage.php`:

```php
class IndexPage extends Page
{
    protected function show()
    {
        // Send initial message
    }

    protected function answer(IncomingRegularMessageInterface $message)
    {
        // Send answers or/and go to another page
    }
}
```
Also, you can specify the directory where the page class will be created:

```sh [artisan]
$ php artisan threadflow:page Auth\\EnterLoginPage
```

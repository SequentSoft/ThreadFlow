# Basic Pages Usage

How to create a basic page and define its behavior.

## Show

The `show` method describes the logic for sending the initial message to the user when the page is opened.

```php
use SequentSoft\ThreadFlow\Messages\Outgoing\Regular\TextOutgoingMessage;

// ...

public function show(): void
{
    TextOutgoingMessage::make(
        'Hello! What\'s Homer Simpson\'s favorite food?'
    )->reply();
}
```

## Answer

The `answer` method describes the logic for processing the user's response to the initial message.

### Reply with a text message

```php
public function answer(IncomingRegularMessageInterface $message): void
{
    if ($message->isText('Donuts')) {
        TextOutgoingMessage::make('Right! Mmm, donuts.')->reply();
        return;
    }
    
    if ($message->isText('Duff')) {
        TextOutgoingMessage::make(
            'Close! Duff is his favorite beer, '
                . 'but donuts take the cake... or the donut.'
        )->reply();
        return;
    }
    
    TextOutgoingMessage::make('Nope, it\'s donuts. D\'oh!')->reply();
}
```

### Go to another page

```php
public function answer(IncomingRegularMessageInterface $message): void
{
    if ($message->isText('Donuts')) {
        TextOutgoingMessage::make('Right! Mmm, donuts.')->reply();
        
        return $this->next(LoginPage::class); // [!code highlight]
    }
    
    TextOutgoingMessage::make('You can\'t login, sorry.')->reply();
}
```

## Welcome

Some drivers support sending a welcome message when the user opens the chat for the first time.

If `welcome` method is defined, it will be called when the user opens the chat for the first time.
Otherwise, the `show` method will be called.

```php
public function welcome(): void
{
    TextOutgoingMessage::make(
        'Welcome! You can ask me anything.'
    )->reply();
}
```

## Dependency Injection

You can inject dependencies into the methods of the page. Laravel's service container will automatically resolve the dependencies.

```php
use App\Services\ChuckNorrisJokesService;

class IndexPage extends Page
{
    public function show(ChuckNorrisJokesService $jokesService): void
    {
        TextOutgoingMessage::make(
            $jokesService->getRandomJoke()
        )->reply();
    }
}
```

## Show page to any bot user

Sometimes you need to redirect the user to specific page outside the conversation flow.
For example, you may want to show a page to the user when something happens in your application.

You can show a page to any bot user using the `showPage` method.

```php
ThreadFlowBot::channel('telegram')
    ->forParticipant('telegram-user-id-1')
    ->showPage(ChatWithAdminPage::class, ['value-1' => 'Hello!']);
```
::: tip Note
You can use the `forParticipant` method to send a message to a specific user
or the `forRoom` method to show a page to a specific room.
:::

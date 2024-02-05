# Exceptions Handling

ThreadFlowBot provides a way to handle exceptions that occur during the processing of messages.
You can use the `ThreadFlowBot::handleProcessingExceptions` method to handle exceptions globally.

For example, you can add the following code to the `boot` method of the `App\Providers\AppServiceProvider` class:

```php
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Laravel\Facades\ThreadFlowBot;

// ...

ThreadFlowBot::handleProcessingExceptions(
    static function (
        string $channelName,
        Throwable $exception,
        SessionInterface $session,
        MessageContextInterface $messageContext
    ) {
        $info = config('app.debug', false)
            ? "\n" . $exception->getMessage()
            : '';

        ThreadFlowBot::channel($channelName)
            ->forParticipant($messageContext->getParticipant())
            ->forRoom($messageContext->getRoom())
            ->sendMessage(
                "Sorry, something went wrong. Please try again later. {$info}"
            );
    }
);
```

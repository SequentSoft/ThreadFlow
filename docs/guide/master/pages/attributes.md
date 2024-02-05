# Attributes

Pages can have attributes. Attributes are used to store data that is needed to process the user's input.

It can be stored inside the page class as a property or passed as a parameter to the `next` method.

## Define attributes

You can define attributes in the page class as properties:

```php
class RegistrationPage extends Page
{
    protected ?string $name = null;
    
    protected ?int $age = null;

    // ...
}
```

## Pass attributes to the next page

You can pass attributes to the next page using the `next` method:

```php
public function answer(IncomingRegularMessageInterface $message)
{
    return $this->next(FinishRegistrationPage::class, [
        'name' => $this->name,
        'age' => $this->age,
        'password' => $message->getText(),
    ]);
}
```
And then you can access these attributes in the next page:

```php
class FinishRegistrationPage extends Page
{
    protected string $name;
    
    protected int $age;
    
    protected string $password;

    public function show()
    {
        TextOutgoingMessage::make(
            "Your name is {$this->name}, you are {$this->age} years old, "
                . "and your password is {$this->password}"
        )->reply();
    }
    
    // ...
}
```

## Store objects in attributes

You can store objects in attributes.
For example, the incoming message to process it later:

```php
class RegistrationPage extends Page
{
    protected ?IncomingRegularMessageInterface $incomingMessage = null;
    
    // ...

    public function answer(IncomingRegularMessageInterface $message)
    {
        if ($this->incomingMessage === null) {
            $this->incomingMessage = $message;
            TextOutgoingMessage::make('Forward this?')->reply();
            return;
        }
        
        if ($message->isText('yes')) {
            ForwardOutgoingMessage::make($this->incomingMessage)->reply();
            return;
        }
    }
}
```

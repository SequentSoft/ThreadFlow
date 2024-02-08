# Session

ThreadFlow uses sessions to store data and state between requests.

## Drivers

ThreadFlow supports the following session drivers:

### Array

With the `array` driver, session data will be stored in memory for the current request.
Usually, this is fine for testing purposes.

### Cache

With the `cache` driver, session data will be stored in the cache. 
Laravel provides a variety of cache drivers, so make sure you have configured one before using it.

### Eloquent

With the `eloquent` driver, session data will be stored in the database.

## Managing Session Data

You can manage session data using the `session` method:

### `set(string $key, mixed $data): void`

You can store data in the session using the `set` method and retrieve it later:

```php
public function answer(IncomingRegularMessageInterface $message)
{
    $this->session()
        ->set('lastMessageText', $message->getText());
}
```

### `get(string $key, mixed $default = null): mixed`

You can retrieve data from the session using the `get` method:

```php
public function show()
{
    $lastMessageText = $this->session()
        ->get('lastMessageText', 'No messages yet');
}
```

### `all(): array`

You can retrieve all data from the session using the `all` method:

```php
public function show()
{
    $allData = $this->session()
        ->all();
}
```

### `delete(string $key): void`

You can remove data from the session using the `delete` method:

```php
public function answer(IncomingRegularMessageInterface $message)
{
    $this->session()
        ->delete('lastMessageText');
}
```

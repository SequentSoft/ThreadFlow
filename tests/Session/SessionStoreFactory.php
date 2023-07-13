<?php

use SequentSoft\ThreadFlow\Session\SessionStoreFactory;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;

it('registers a session store', function () {
    $factory = new SessionStoreFactory();
    $factory->register('array', function ($channelName, ConfigInterface $config) {
        return new \SequentSoft\ThreadFlow\Session\ArraySessionStore($channelName, $config, new \SequentSoft\ThreadFlow\Session\ArraySessionStoreStorage());
    });

    // Access the protected property using ReflectionClass
    $reflection = new ReflectionClass(SessionStoreFactory::class);
    $registeredSessionStores = $reflection->getProperty('registeredSessionStores');
    $registeredSessionStores->setAccessible(true);

    // Assert that the session store is registered
    expect($registeredSessionStores->getValue($factory))->toHaveKey('array');
    expect($registeredSessionStores->getValue($factory)['array'])->toBeInstanceOf(Closure::class);
});
it('makes a session store', function () {
    $factory = new SessionStoreFactory();
    $factory->register('array', function ($channelName, ConfigInterface $config) {
        return new \SequentSoft\ThreadFlow\Session\ArraySessionStore($channelName, $config, new \SequentSoft\ThreadFlow\Session\ArraySessionStoreStorage());
    });

    // Mock the dependencies
    $config = $this->createMock(ConfigInterface::class);

    // Make the session store
    $sessionStore = $factory->make('array', 'channel_name', $config);

    // Assert that the session store is created
    expect($sessionStore)->toBeInstanceOf(SessionStoreInterface::class);
});

it('throws exception when trying to make an unregistered session store', function () {
    $factory = new SessionStoreFactory();

    // Mock the dependencies
    $config = $this->createMock(ConfigInterface::class);

    // Call make() with an unregistered session store name
    $factory->make('invalid_store', 'channel_name', $config);
})->throws(InvalidArgumentException::class, 'Session store invalid_store is not registered.');

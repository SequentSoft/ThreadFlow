<?php

use SequentSoft\ThreadFlow\Dispatcher\DispatcherFactory;
use SequentSoft\ThreadFlow\Dispatcher\Laravel\LaravelQueueIncomingDispatcher;

it('registers and makes a dispatcher', function () {
    $factory = new DispatcherFactory;

    $factory->register('sync', function () {
        return new LaravelQueueIncomingDispatcher();
    });

    $dispatcher = $factory->make('sync');

    $this->assertInstanceOf(LaravelQueueIncomingDispatcher::class, $dispatcher);
});

it('throws exception when dispatcher is not registered', function () {
    $factory = new DispatcherFactory;

    $this->expectException(InvalidArgumentException::class);

    $factory->make('non-existing');
});

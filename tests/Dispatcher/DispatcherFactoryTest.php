<?php

use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Dispatcher\DispatcherFactory;

it('can be created', function () {
    $factory = new DispatcherFactory();

    expect($factory)->toBeInstanceOf(DispatcherFactoryInterface::class);
});

it('can register a dispatcher', function () {
    $factory = new DispatcherFactory();

    $factory->register('test', function () {
        return new class implements DispatcherInterface {
            public function dispatch(
                BotInterface $bot,
                IncomingMessageInterface $message,
                ?Closure $incomingCallback = null,
                ?Closure $outgoingCallback = null
            ): void {
                //
            }
        };
    });

    expect($factory->make('test'))->toBeInstanceOf(DispatcherInterface::class);
});

it('throws an exception when trying to make a non-registered dispatcher', function () {
    $factory = new DispatcherFactory();

    $factory->make('test');
})->throws(InvalidArgumentException::class);
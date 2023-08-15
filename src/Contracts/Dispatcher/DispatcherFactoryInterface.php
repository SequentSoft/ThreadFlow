<?php

namespace SequentSoft\ThreadFlow\Contracts\Dispatcher;

use Closure;
use SequentSoft\ThreadFlow\Contracts\BotInterface;

interface DispatcherFactoryInterface
{
    /**
     * Register a dispatcher with the factory.
     *
     * @param string $name The name of the dispatcher.
     * @param Closure $callback A closure that returns a new instance of the dispatcher.
     * @return void
     */
    public function register(string $name, Closure $callback): void;

    /**
     * Make a dispatcher instance.
     *
     * @param string $name The name of the dispatcher.
     * @return DispatcherInterface The dispatcher instance.
     */
    public function make(string $name): DispatcherInterface;
}

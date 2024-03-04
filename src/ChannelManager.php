<?php

declare(strict_types=1);

namespace SequentSoft\ThreadFlow;

use Closure;
use RuntimeException;
use SequentSoft\ThreadFlow\Contracts\Channel\ChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\ChannelManagerInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;
use SequentSoft\ThreadFlow\Exceptions\Config\InvalidNestedConfigException;
use SequentSoft\ThreadFlow\Traits\HandleExceptions;
use SequentSoft\ThreadFlow\Traits\HasUserResolver;

class ChannelManager implements ChannelManagerInterface
{
    use HandleExceptions;
    use HasUserResolver;

    /**
     * Array of registered channel drivers.
     * @var array<string, Closure>
     */
    protected array $channelDrivers = [];

    /**
     * Array of instantiated channels.
     * @var array<string, ChannelInterface>
     */
    protected array $channels = [];

    public function __construct(
        protected ConfigInterface $config,
        protected SessionStoreFactoryInterface $sessionStoreFactory,
        protected DispatcherFactoryInterface $dispatcherFactory,
        protected EventBusInterface $eventBus,
    ) {
    }

    /**
     * Get all registered channel drivers.
     */
    public function getRegisteredChannelDrivers(): array
    {
        return $this->channelDrivers;
    }

    /**
     * Register a channel driver.
     */
    public function registerChannelDriver(string $channelName, Closure $callback): void
    {
        $this->channelDrivers[$channelName] = $callback;
    }

    /**
     * Make a channel instance.
     *
     * @throws RuntimeException
     * @throws InvalidNestedConfigException
     */
    protected function makeChannel(string $channelName): ChannelInterface
    {
        $config = $this->getChannelConfig($channelName);

        $driverName = $config->get('driver');

        $channelDriver = $this->channelDrivers[$driverName] ?? null;

        if (is_null($channelDriver)) {
            throw new RuntimeException("Channel driver for channel \"{$driverName}\" is not registered");
        }

        $config = $this->getChannelConfig($channelName);

        return $channelDriver(
            $channelName,
            $config,
            $this->getSessionStore($channelName),
            $this->getDispatcherFactory(),
            $this->eventBus->nested($channelName),
        );
    }

    protected function getDispatcherFactory(): DispatcherFactoryInterface
    {
        return $this->dispatcherFactory;
    }

    /**
     * Get the config for a channel by name.
     */
    protected function getChannelConfig(string $channelName): ConfigInterface
    {
        return $this->config
            ->getNested('channels')
            ->getNested($channelName);
    }

    /**
     * Register a callback to be executed when an event is fired.
     * The callback will receive the event instance.
     * @param class-string<EventInterface> $event
     */
    public function on(string $event, callable $callback): void
    {
        $this->eventBus->listen($event, $callback);
    }

    /**
     * Get the session store for a channel by name.
     */
    protected function getSessionStore(string $channelName): SessionStoreInterface
    {
        $channelConfig = $this->getChannelConfig($channelName);

        return $this->sessionStoreFactory->make(
            $channelConfig->get('session'),
            $channelName,
        );
    }

    /**
     * Get a channel instance by name.
     * If the channel is not instantiated, it will be created.
     * @throws InvalidNestedConfigException
     */
    public function channel(string $channelName): ChannelInterface
    {
        if (isset($this->channels[$channelName])) {
            return $this->channels[$channelName];
        }

        $channel = $this->makeChannel($channelName);

        $channel->setUserResolver($this->userResolver);

        $channel->registerExceptionHandler($this->handleException(...));

        $this->channels[$channelName] = $channel;

        return $channel;
    }
}

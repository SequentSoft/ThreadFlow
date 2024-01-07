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

class ChannelManager implements ChannelManagerInterface
{
    use HandleExceptions;

    protected array $channelDrivers = [];

    protected array $channels = [];

    public function __construct(
        protected ConfigInterface $config,
        protected SessionStoreFactoryInterface $sessionStoreFactory,
        protected DispatcherFactoryInterface $dispatcherFactory,
        protected EventBusInterface $eventBus,
    ) {
    }

    public function getRegisteredChannelDrivers(): array
    {
        return $this->channelDrivers;
    }

    public function registerChannelDriver(string $channelName, Closure $callback): void
    {
        $this->channelDrivers[$channelName] = $callback;
    }

    protected function makeChannel(string $channelName): ChannelInterface
    {
        $channelDriver = $this->channelDrivers[$channelName] ?? null;

        if (is_null($channelDriver)) {
            throw new RuntimeException("Channel driver for channel \"{$channelName}\" is not registered");
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

    /** @deprecated Use "registerExceptionHandler" method */
    public function handleProcessingExceptions(Closure $callback): void
    {
        $this->registerExceptionHandler($callback);
    }

    /**
     * @throws InvalidNestedConfigException
     */
    protected function getChannelConfig(string $channelName): ConfigInterface
    {
        return $this->config
            ->getNested('channels')
            ->getNested($channelName);
    }

    /**
     * @param  class-string<EventInterface>  $event
     */
    public function on(string $event, callable $callback): void
    {
        $this->eventBus->listen($event, $callback);
    }

    /**
     * @throws InvalidNestedConfigException
     */
    protected function getSessionStore(string $channelName): SessionStoreInterface
    {
        $channelConfig = $this->getChannelConfig($channelName);

        return $this->sessionStoreFactory->make(
            $channelConfig->get('session'),
            $channelName,
            $channelConfig
        );
    }

    public function channel(string $channelName): ChannelInterface
    {
        if (isset($this->channels[$channelName])) {
            return $this->channels[$channelName];
        }

        $channel = $this->makeChannel($channelName);

        $channel->registerExceptionHandler($this->handleException(...));

        return $channel;
    }
}

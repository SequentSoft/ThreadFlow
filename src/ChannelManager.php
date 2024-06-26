<?php

declare(strict_types=1);

namespace SequentSoft\ThreadFlow;

use Closure;
use RuntimeException;
use SequentSoft\ThreadFlow\Contracts\Channel\ChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\ChannelManagerInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventInterface;
use SequentSoft\ThreadFlow\Contracts\Page\ActivePagesRepositoryInterface;
use SequentSoft\ThreadFlow\Contracts\Page\ActivePagesStorageFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\PendingMessages\PendingMessagesRepositoryInterface;
use SequentSoft\ThreadFlow\Contracts\PendingMessages\PendingMessagesStorageFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;
use SequentSoft\ThreadFlow\Page\ActivePages\ActivePagesRepository;
use SequentSoft\ThreadFlow\PendingMessages\PendingMessagesRepository;
use SequentSoft\ThreadFlow\Traits\HandleExceptions;
use SequentSoft\ThreadFlow\Traits\HasUserResolver;

class ChannelManager implements ChannelManagerInterface
{
    use HandleExceptions;
    use HasUserResolver;

    /**
     * Array of registered channel drivers.
     *
     * @var array<string, Closure>
     */
    protected array $channelDrivers = [];

    /**
     * Array of instantiated channels.
     *
     * @var array<string, ChannelInterface>
     */
    protected array $channels = [];

    public function __construct(
        protected ConfigInterface $config,
        protected SessionStoreFactoryInterface $sessionStoreFactory,
        protected DispatcherFactoryInterface $dispatcherFactory,
        protected PendingMessagesStorageFactoryInterface $pendingMessagesStorageFactory,
        protected ActivePagesStorageFactoryInterface $activePagesStorageFactory,
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
    public function registerChannelDriver(string $driverName, Closure $callback): void
    {
        $this->channelDrivers[$driverName] = $callback;
    }

    /**
     * Make a channel instance.
     *
     * @throws RuntimeException
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

        $nestedEventBus = $this->eventBus->nested($channelName);

        $activePagesRepository = $this->getActivePagesRepository(
            $config->get('active_pages', 'array')
        );

        $pendingMessagesRepository = $this->getPendingMessagesRepository(
            $config->get('pending_messages', 'array')
        );

        return call_user_func_array($channelDriver, [
            'channelName' => $channelName,
            'config' => $config,
            'sessionStore' => $this->getSessionStore($channelName),
            'dispatcher' => $this->getDispatcher(
                $config->get('dispatcher'),
                $nestedEventBus,
                $activePagesRepository,
                $pendingMessagesRepository,
            ),
            'eventBus' => $nestedEventBus,
        ]);
    }

    protected function getActivePagesRepository(string $storageDriverName): ActivePagesRepositoryInterface
    {
        return new ActivePagesRepository(
            $this->activePagesStorageFactory->make($storageDriverName)
        );
    }

    protected function getPendingMessagesRepository(string $storageDriverName): PendingMessagesRepositoryInterface
    {
        return new PendingMessagesRepository(
            $this->pendingMessagesStorageFactory->make($storageDriverName)
        );
    }

    protected function getDispatcher(
        string $driver,
        EventBusInterface $eventBus,
        ActivePagesRepositoryInterface $activePagesRepository,
        PendingMessagesRepositoryInterface $pendingMessagesRepository
    ): DispatcherInterface {
        return $this->dispatcherFactory->make(
            $driver,
            $eventBus,
            $activePagesRepository,
            $pendingMessagesRepository,
        );
    }

    /**
     * Get the config for a channel by name.
     */
    protected function getChannelConfig(string $channelName): ConfigInterface
    {
        return $this->config
            ->getNested($channelName);
    }

    /**
     * Register a callback to be executed when an event is fired.
     * The callback will receive the event instance.
     *
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
            $channelConfig->get('session')
        );
    }

    /**
     * Get a channel instance by name.
     * If the channel is not instantiated, it will be created.
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

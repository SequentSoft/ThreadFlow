<?php

namespace SequentSoft\ThreadFlow\Laravel;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use SequentSoft\ThreadFlow\ChannelManager;
use SequentSoft\ThreadFlow\Config;
use SequentSoft\ThreadFlow\Contracts\Channel\ChannelManagerInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Page\ActivePagesStorageFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\PendingMessages\PendingMessagesStorageFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Serializers\SerializerInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreFactoryInterface;
use SequentSoft\ThreadFlow\Dispatcher\DispatcherFactory;
use SequentSoft\ThreadFlow\Dispatcher\SyncDispatcher;
use SequentSoft\ThreadFlow\Events\EventBus;
use SequentSoft\ThreadFlow\Laravel\Console\ActivePagesTableThreadFlowCommand;
use SequentSoft\ThreadFlow\Laravel\Console\GenerateThreadFlowFormCommand;
use SequentSoft\ThreadFlow\Laravel\Console\GenerateThreadFlowPageCommand;
use SequentSoft\ThreadFlow\Laravel\Console\PendingMessagesTableThreadFlowCommand;
use SequentSoft\ThreadFlow\Laravel\Console\SessionTableThreadFlowCommand;
use SequentSoft\ThreadFlow\Laravel\Dispatcher\LaravelQueueIncomingDispatcher;
use SequentSoft\ThreadFlow\Laravel\Page\ActivePages\StorageDrivers\CacheActivePagesStorage;
use SequentSoft\ThreadFlow\Laravel\Page\ActivePages\StorageDrivers\EloquentActivePagesStorage;
use SequentSoft\ThreadFlow\Laravel\PendingMessages\StorageDrivers\CachePendingMessagesStorage;
use SequentSoft\ThreadFlow\Laravel\PendingMessages\StorageDrivers\EloquentPendingMessagesStorage;
use SequentSoft\ThreadFlow\Laravel\Session\CacheSessionStore;
use SequentSoft\ThreadFlow\Laravel\Session\EloquentSessionStore;
use SequentSoft\ThreadFlow\Page\ActivePages\ActivePagesStorageFactory;
use SequentSoft\ThreadFlow\Page\ActivePages\StorageDrivers\ArrayActivePagesStorage;
use SequentSoft\ThreadFlow\Page\ActivePages\StorageDrivers\SessionActivePagesStorage;
use SequentSoft\ThreadFlow\PendingMessages\PendingMessagesStorageFactory;
use SequentSoft\ThreadFlow\PendingMessages\StorageDrivers\ArrayPendingMessagesStorage;
use SequentSoft\ThreadFlow\PendingMessages\StorageDrivers\SessionPendingMessagesStorage;
use SequentSoft\ThreadFlow\Serializers\SimpleSerializer;
use SequentSoft\ThreadFlow\Session\Drivers\ArraySessionStore;
use SequentSoft\ThreadFlow\Session\Drivers\ArraySessionStoreStorage;
use SequentSoft\ThreadFlow\Session\SessionStoreFactory;

class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom($this->getPackageConfigPath(), 'thread-flow');

        $this->app->bind(EventBusInterface::class, EventBus::class);
        $this->app->bind(SerializerInterface::class, SimpleSerializer::class);

        $this->app->singleton(ChannelManagerInterface::class, function () {
            return new ChannelManager(
                new Config(
                    $this->app->make('config')->get('thread-flow.channels', [])
                ),
                $this->app->make(SessionStoreFactoryInterface::class),
                $this->app->make(DispatcherFactoryInterface::class),
                $this->app->make(PendingMessagesStorageFactoryInterface::class),
                $this->app->make(ActivePagesStorageFactoryInterface::class),
                $this->app->make(EventBusInterface::class),
            );
        });

        $this->app->singleton(ArrayPendingMessagesStorage::class, function ($app, $params) {
            return new ArrayPendingMessagesStorage(
                $params['config'],
                $params['serializer'],
            );
        });

        $this->app->singleton(ArrayActivePagesStorage::class, function ($app, $params) {
            return new ArrayActivePagesStorage(
                $params['config'],
                $params['serializer'],
            );
        });

        $this->app->singleton(ArraySessionStoreStorage::class, function () {
            return new ArraySessionStoreStorage();
        });

        $this->app->singleton(PendingMessagesStorageFactoryInterface::class, function () {
            $factory = new PendingMessagesStorageFactory(
                new Config($this->app->make('config')->get('thread-flow.pending_messages', []))
            );

            $factory->registerDriver(
                'array',
                fn (ConfigInterface $config) => $this->app
                    ->make(ArrayPendingMessagesStorage::class, [
                        'config' => $config,
                        'serializer' => $this->app->make(SerializerInterface::class),
                    ])
            );

            $factory->registerDriver(
                'eloquent',
                fn (ConfigInterface $config) => $this->app
                    ->make(EloquentPendingMessagesStorage::class, [
                        'config' => $config,
                        'serializer' => $this->app->make(SerializerInterface::class),
                    ])
            );

            $factory->registerDriver(
                'cache',
                fn (ConfigInterface $config) => $this->app
                    ->make(CachePendingMessagesStorage::class, [
                        'config' => $config,
                        'serializer' => $this->app->make(SerializerInterface::class),
                    ])
            );

            $factory->registerDriver(
                'session',
                fn (ConfigInterface $config) => $this->app
                    ->make(SessionPendingMessagesStorage::class, [
                        'config' => $config,
                        'serializer' => $this->app->make(SerializerInterface::class),
                    ])
            );

            return $factory;
        });

        $this->app->singleton(ActivePagesStorageFactoryInterface::class, function () {
            $factory = new ActivePagesStorageFactory(
                new Config($this->app->make('config')->get('thread-flow.active_pages', []))
            );

            $factory->registerDriver(
                'array',
                fn (ConfigInterface $config) => $this->app
                    ->make(ArrayActivePagesStorage::class, [
                        'config' => $config,
                        'serializer' => $this->app->make(SerializerInterface::class),
                    ])
            );

            $factory->registerDriver(
                'eloquent',
                fn (ConfigInterface $config) => $this->app
                    ->make(EloquentActivePagesStorage::class, [
                        'config' => $config,
                        'serializer' => $this->app->make(SerializerInterface::class),
                    ])
            );

            $factory->registerDriver(
                'cache',
                fn (ConfigInterface $config) => $this->app
                    ->make(CacheActivePagesStorage::class, [
                        'config' => $config,
                        'serializer' => $this->app->make(SerializerInterface::class),
                    ])
            );

            $factory->registerDriver(
                'session',
                fn (ConfigInterface $config) => $this->app
                    ->make(SessionActivePagesStorage::class, [
                        'config' => $config,
                        'serializer' => $this->app->make(SerializerInterface::class),
                    ])
            );

            return $factory;
        });

        $this->app->singleton(SessionStoreFactoryInterface::class, function () {
            $factory = new SessionStoreFactory(
                new Config($this->app->make('config')->get('thread-flow.sessions', []))
            );

            $factory->registerDriver(
                'array',
                fn (ConfigInterface $config) => new ArraySessionStore(
                    $config,
                    $this->app->make(SerializerInterface::class),
                    $this->app->make(ArraySessionStoreStorage::class),
                )
            );

            $factory->registerDriver(
                'cache',
                fn (ConfigInterface $config) => new CacheSessionStore(
                    $config,
                    $this->app->make(SerializerInterface::class),
                )
            );

            $factory->registerDriver(
                'eloquent',
                fn (ConfigInterface $config) => new EloquentSessionStore(
                    $config,
                    $this->app->make(SerializerInterface::class),
                )
            );

            return $factory;
        });

        $this->app->singleton(DispatcherFactoryInterface::class, function () {
            $factory = new DispatcherFactory(
                new Config($this->app->make('config')->get('thread-flow.dispatchers', []))
            );
            $factory->registerDriver(
                'sync',
                fn (...$dependencies) => new SyncDispatcher(...$dependencies)
            );
            $factory->registerDriver(
                'queue',
                fn (...$dependencies) => new LaravelQueueIncomingDispatcher(...$dependencies)
            );

            return $factory;
        });
    }

    protected function getPackageConfigPath(): string
    {
        return __DIR__ . '/config.php';
    }

    public function boot(): void
    {
        $this->publishes([
            $this->getPackageConfigPath() => $this->app->configPath('thread-flow.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateThreadFlowPageCommand::class,
                GenerateThreadFlowFormCommand::class,
                SessionTableThreadFlowCommand::class,
                ActivePagesTableThreadFlowCommand::class,
                PendingMessagesTableThreadFlowCommand::class,
            ]);
        }
    }
}

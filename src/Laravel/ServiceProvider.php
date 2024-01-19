<?php

namespace SequentSoft\ThreadFlow\Laravel;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use SequentSoft\ThreadFlow\ChannelManager;
use SequentSoft\ThreadFlow\Config;
use SequentSoft\ThreadFlow\Contracts\Channel\ChannelManagerInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreFactoryInterface;
use SequentSoft\ThreadFlow\Dispatcher\DispatcherFactory;
use SequentSoft\ThreadFlow\Laravel\Dispatcher\LaravelQueueIncomingDispatcher;
use SequentSoft\ThreadFlow\Dispatcher\SyncDispatcher;
use SequentSoft\ThreadFlow\Events\EventBus;
use SequentSoft\ThreadFlow\Laravel\Console\CliThreadFlowCommand;
use SequentSoft\ThreadFlow\Laravel\Console\GenerateThreadFlowPageCommand;
use SequentSoft\ThreadFlow\Session\ArraySessionStore;
use SequentSoft\ThreadFlow\Session\ArraySessionStoreStorage;
use SequentSoft\ThreadFlow\Laravel\Session\CacheSessionStore;
use SequentSoft\ThreadFlow\Session\SessionStoreFactory;

class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom($this->getPackageConfigPath(), 'thread-flow');

        $this->app->bind(EventBusInterface::class, EventBus::class);

        $this->app->singleton(ChannelManagerInterface::class, function () {
            return new ChannelManager(
                new Config($this->app->make('config')->get('thread-flow', [])),
                $this->app->make(SessionStoreFactoryInterface::class),
                $this->app->make(DispatcherFactoryInterface::class),
                $this->app->make(EventBusInterface::class),
            );
        });

        $this->app->singleton(ArraySessionStoreStorage::class, ArraySessionStoreStorage::class);

        $this->app->singleton(SessionStoreFactoryInterface::class, function () {
            $factory = new SessionStoreFactory();

            $factory->register('array', fn (string $channelName) => new ArraySessionStore(
                $channelName,
                $this->app->make(ArraySessionStoreStorage::class),
            ));

            $factory->register(
                'cache',
                fn (string $channelName, ConfigInterface $config) => new CacheSessionStore(
                    $channelName,
                    $config,
                )
            );

            return $factory;
        });

        $this->app->singleton(DispatcherFactoryInterface::class, function () {
            $factory = new DispatcherFactory();
            $factory->register(
                'sync',
                fn ($channelName, $eventBus, $defaultPageClass, $outgoing) => new SyncDispatcher(
                    $channelName,
                    $eventBus,
                    $defaultPageClass,
                    $outgoing
                )
            );
            $factory->register(
                'queue',
                fn ($channelName, $eventBus, $defaultPageClass, $outgoing) => new LaravelQueueIncomingDispatcher(
                    $channelName,
                    $eventBus,
                    $defaultPageClass,
                    $outgoing
                )
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
            $this->getPackageConfigPath() => $this->app->configPath('config.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateThreadFlowPageCommand::class,
                CliThreadFlowCommand::class,
            ]);
        }
    }
}

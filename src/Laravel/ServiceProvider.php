<?php

namespace SequentSoft\ThreadFlow\Laravel;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use SequentSoft\ThreadFlow\ChannelManager;
use SequentSoft\ThreadFlow\Config;
use SequentSoft\ThreadFlow\Contracts\Channel\ChannelManagerInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreFactoryInterface;
use SequentSoft\ThreadFlow\Dispatcher\DispatcherFactory;
use SequentSoft\ThreadFlow\Laravel\Console\SessionTableThreadFlowCommand;
use SequentSoft\ThreadFlow\Laravel\Dispatcher\LaravelQueueIncomingDispatcher;
use SequentSoft\ThreadFlow\Dispatcher\SyncDispatcher;
use SequentSoft\ThreadFlow\Events\EventBus;
use SequentSoft\ThreadFlow\Laravel\Console\CliThreadFlowCommand;
use SequentSoft\ThreadFlow\Laravel\Console\GenerateThreadFlowPageCommand;
use SequentSoft\ThreadFlow\Laravel\Page\PageFactory;
use SequentSoft\ThreadFlow\Laravel\Session\EloquentSessionStore;
use SequentSoft\ThreadFlow\Session\ArraySessionStore;
use SequentSoft\ThreadFlow\Session\ArraySessionStoreStorage;
use SequentSoft\ThreadFlow\Laravel\Session\CacheSessionStore;
use SequentSoft\ThreadFlow\Session\SessionStoreFactory;

class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom($this->getPackageConfigPath(), 'thread-flow');

        $this->app->bind(PageFactoryInterface::class, PageFactory::class);

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
            $factory = new SessionStoreFactory(
                new Config($this->app->make('config')->get('thread-flow.sessions', []))
            );

            $factory->registerDriver('array', fn (string $channelName) => new ArraySessionStore(
                $channelName,
                $this->app->make(ArraySessionStoreStorage::class),
            ));

            $factory->registerDriver(
                'cache',
                fn (string $channelName, ConfigInterface $config) => new CacheSessionStore(
                    $channelName,
                    $config,
                )
            );

            $factory->registerDriver(
                'eloquent',
                fn (string $channelName, ConfigInterface $config) => new EloquentSessionStore(
                    $channelName,
                    $config,
                )
            );

            return $factory;
        });

        $this->app->singleton(DispatcherFactoryInterface::class, function () {
            $factory = new DispatcherFactory(
                $this->app->make(PageFactoryInterface::class),
                new Config($this->app->make('config')->get('thread-flow.dispatchers', []))
            );
            $factory->registerDriver(
                'sync',
                fn ($pageFactory, $eventBus, $defaultPageClass, $outgoing) => new SyncDispatcher(
                    $pageFactory,
                    $eventBus,
                    $defaultPageClass,
                    $outgoing
                )
            );
            $factory->registerDriver(
                'queue',
                fn ($pageFactory, $eventBus, $defaultPageClass, $outgoing) => new LaravelQueueIncomingDispatcher(
                    $pageFactory,
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
            $this->getPackageConfigPath() => $this->app->configPath('thread-flow.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateThreadFlowPageCommand::class,
                CliThreadFlowCommand::class,
                SessionTableThreadFlowCommand::class,
            ]);
        }
    }
}

<?php

namespace SequentSoft\ThreadFlow;

use Illuminate\Support\ServiceProvider;
use SequentSoft\ThreadFlow\Channel\Incoming\IncomingChannelRegistry;
use SequentSoft\ThreadFlow\Channel\Outgoing\OutgoingChannelRegistry;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Incoming\IncomingChannelRegistryInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Outgoing\OutgoingChannelRegistryInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Router\RouterInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreFactoryInterface;
use SequentSoft\ThreadFlow\Dispatcher\DispatcherFactory;
use SequentSoft\ThreadFlow\Dispatcher\Laravel\LaravelQueueIncomingDispatcher;
use SequentSoft\ThreadFlow\Dispatcher\SyncIncomingDispatcher;
use SequentSoft\ThreadFlow\Router\StatefulPageRouter;
use SequentSoft\ThreadFlow\Session\ArraySessionStore;
use SequentSoft\ThreadFlow\Session\ArraySessionStoreStorage;
use SequentSoft\ThreadFlow\Laravel\Console\GenerateThreadFlowPageCommand;
use SequentSoft\ThreadFlow\Session\Laravel\LaravelCacheSessionStore;
use SequentSoft\ThreadFlow\Session\SessionStoreFactory;
use SequentSoft\ThreadFlow\Laravel\Console\CliThreadFlowCommand;

class LaravelServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom($this->getPackageConfigPath(), 'thread-flow');

        $this->app->bind(RouterInterface::class, StatefulPageRouter::class);

        $this->app->bind(BotInterface::class, function () {
            return new ThreadFlowBot(
                new Config($this->app->make('config')->get('thread-flow', [])),
                $this->app->make(SessionStoreFactoryInterface::class),
                $this->app->make(RouterInterface::class),
            );
        });

        $this->app->singleton(ArraySessionStoreStorage::class, ArraySessionStoreStorage::class);

        $this->app->singleton(SessionStoreFactoryInterface::class, function () {
            $factory = new SessionStoreFactory();

            $factory->register(
                'array',
                function (string $channelName, ConfigInterface $config) {
                    return $this->app->make(
                        ArraySessionStore::class,
                        ['channelName' => $channelName, 'config' => $config]
                    );
                }
            );

            $factory->register(
                'cache',
                function (string $channelName, ConfigInterface $config) {
                    return $this->app->make(
                        LaravelCacheSessionStore::class,
                        ['channelName' => $channelName, 'config' => $config]
                    );
                }
            );

            return $factory;
        });

        $this->app->singleton(DispatcherFactoryInterface::class, function () {
            $factory = new DispatcherFactory();

            $factory->register('sync', function () {
                return new SyncIncomingDispatcher(
                    $this->app->make(BotInterface::class)
                );
            });

            $factory->register('queue', function () {
                return new LaravelQueueIncomingDispatcher();
            });

            return $factory;
        });

        $this->app->singleton(IncomingChannelRegistryInterface::class, IncomingChannelRegistry::class);

        $this->app->singleton(OutgoingChannelRegistryInterface::class, OutgoingChannelRegistry::class);
    }

    protected function getPackageConfigPath(): string
    {
        return __DIR__ . '/../config/thread-flow.php';
    }

    public function boot()
    {
        $this->publishes([
            $this->getPackageConfigPath() => $this->app->configPath('thread-flow.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateThreadFlowPageCommand::class,
                CliThreadFlowCommand::class,
            ]);
        }
    }
}

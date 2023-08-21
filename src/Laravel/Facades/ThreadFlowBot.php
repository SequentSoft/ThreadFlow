<?php

namespace SequentSoft\ThreadFlow\Laravel\Facades;

use Closure;
use Illuminate\Support\Facades\Facade;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\BotManagerInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Incoming\IncomingChannelRegistryInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Outgoing\OutgoingChannelRegistryInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Router\RouterInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreFactoryInterface;
use SequentSoft\ThreadFlow\Testing\FakeBotManager;
use SequentSoft\ThreadFlow\Config;

/**
 * @method static ConfigInterface getChannelConfig(string $channelName)
 * @method static ConfigInterface getConfig()
 * @method static array getAvailableChannels()
 * @method static void on(string $event, callable $callback)
 * @method static BotInterface channel(string $channelName)
 * @method static void assertSentOutgoingMessageCount(int $count)
 * @method static void assertSentOutgoingMessage(Closure $callback)
 * @method static void assertDispatchedPageCount(int $count)
 * @method static void assertDispatchedPage(Closure $callback)
 * @method static void assertDispatchedPageClass(string $pageClass)
 * @method static void assertNotingDispatched()
 * @method static void assertNotingSent()
 * @method static void assertSentTextMessage(string $text)
 * @method static void assertCurrentPageClass(string $pageClass)
 * @method static void assertCurrentPage(callable $callback)
 * phpcs:enable
 */
class ThreadFlowBot extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return BotManagerInterface::class;
    }

    public static function fake()
    {
        $fakeBotManager = new FakeBotManager(
            new Config(static::$app->make('config')->get('thread-flow', [])),
            static::$app->make(SessionStoreFactoryInterface::class),
            static::$app->make(RouterInterface::class),
            static::$app->make(OutgoingChannelRegistryInterface::class),
            static::$app->make(IncomingChannelRegistryInterface::class),
            static::$app->make(DispatcherFactoryInterface::class),
            static::$app->make(EventBusInterface::class),
        );

        static::swap($fakeBotManager);

        return $fakeBotManager;
    }
}

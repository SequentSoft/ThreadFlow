<?php

namespace SequentSoft\ThreadFlow\Laravel\Facades;

use Closure;
use Illuminate\Support\Facades\Facade;
use SequentSoft\ThreadFlow\Config;
use SequentSoft\ThreadFlow\Contracts\Channel\ChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\ChannelManagerInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Testing\ResultsRecorderInterface;
use SequentSoft\ThreadFlow\Dispatcher\FakeDispatcherFactory;
use SequentSoft\ThreadFlow\Testing\FakeChannelManager;

/**
 * @method static array getAvailableChannels()
 * @method static void on(string $event, callable $callback)
 * @method static ChannelInterface channel(string $channelName)
 * @method static void registerExceptionHandler(Closure $callback)
 * @method static void disableExceptionsHandlers()
 * @method static ResultsRecorderInterface assertState(string $pageClass, ?string $method = null, ?array $attributes = null)
 * @method static ResultsRecorderInterface assertOutgoingMessagesCount(int $count)
 * @method static ResultsRecorderInterface assertOutgoingMessage(Closure $callback, ?int $index = null)
 * @method static ResultsRecorderInterface assertDispatchedPagesCount(int $count)
 * @method static ResultsRecorderInterface assertDispatchedPage(Closure $callback, ?int $index = null)
 * @method static ResultsRecorderInterface assertOutgoingMessageText(string $text, ?int $index = null)
 * @method static ResultsRecorderInterface assertOutgoingMessageTextContains(string $text, ?int $index = null)
 */
class ThreadFlowBot extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return ChannelManagerInterface::class;
    }

    public static function fake()
    {
        $fakeChannelManager = new FakeChannelManager(
            new Config(static::$app->make('config')->get('thread-flow', [])),
            static::$app->make(SessionStoreFactoryInterface::class),
            static::$app->make(DispatcherFactoryInterface::class),
            static::$app->make(EventBusInterface::class),
        );

        $fakeChannelManager->setFakeDispatcherFactory(
            new FakeDispatcherFactory(static::$app->make(PageFactoryInterface::class))
        );

        $originalChannelManager = static::$app->make(ChannelManagerInterface::class);

        foreach ($originalChannelManager->getRegisteredChannelDrivers() as $channelName => $channelDriver) {
            $fakeChannelManager->registerChannelDriver($channelName, $channelDriver);
        }

        foreach ($originalChannelManager->getExceptionsHandlers() as $handler) {
            $fakeChannelManager->registerExceptionHandler($handler);
        }

        static::swap($fakeChannelManager);

        return $fakeChannelManager;
    }
}

<?php

namespace SequentSoft\ThreadFlow\Laravel\Facades;

use Closure;
use Illuminate\Support\Facades\Facade;
use SequentSoft\ThreadFlow\Config;
use SequentSoft\ThreadFlow\Contracts\Channel\ChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\ChannelManagerInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreFactoryInterface;
use SequentSoft\ThreadFlow\Testing\FakeChannelManager;
use SequentSoft\ThreadFlow\Testing\ResultsRecorder;

/**
 * @method static array getAvailableChannels()
 * @method static void on(string $event, callable $callback)
 * @method static ChannelInterface channel(string $channelName)
 * @method static void registerExceptionHandler(Closure $callback)
 * @method static void disableExceptionsHandlers()
 *
 * @method static ResultsRecorder assertState(string $pageClass, ?string $method = null, ?array $attributes = null)
 * @method static ResultsRecorder assertOutgoingMessagesCount(int $count)
 * @method static ResultsRecorder assertOutgoingMessage(Closure $callback, ?int $index = null)
 * @method static ResultsRecorder assertDispatchedPagesCount(int $count)
 * @method static ResultsRecorder assertDispatchedPage(Closure $callback, ?int $index = null)
 * @method static ResultsRecorder assertOutgoingMessageText(string $text, ?int $index = null)
 * @method static ResultsRecorder assertOutgoingMessageTextContains(string $text, ?int $index = null)
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

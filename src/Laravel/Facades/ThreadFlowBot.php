<?php

namespace SequentSoft\ThreadFlow\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\BotManagerInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;

/**
 * @method static ConfigInterface getChannelConfig(string $channelName)
 * @method static ConfigInterface getConfig()
 * @method static array getAvailableChannels()
 * @method static void on(string $event, callable $callback)
 * @method static BotInterface channel(string $channelName)
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
}

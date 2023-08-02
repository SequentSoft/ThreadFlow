<?php

namespace SequentSoft\ThreadFlow\Laravel\Facades;

use Closure;
use Illuminate\Support\Facades\Facade;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;

/**
 * phpcs:disable Generic.Files.LineLength
 * @method static void showPage(string $channelName, MessageContextInterface|string $context, string $pageClass, array $pageAttributes = [])
 * @method static void process(string $channelName, IncomingMessageInterface $message, ?Closure $incomingCallback = null, ?Closure $outgoingCallback = null)
 * @method static void incoming(string $channelName, Closure $callback)
 * @method static void outgoing(string $channelName, Closure $callback)
 * @method static ConfigInterface getChannelConfig(string $channelName)
 * @method static array getAvailableChannels()
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
        return BotInterface::class;
    }
}

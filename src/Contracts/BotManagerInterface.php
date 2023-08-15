<?php

namespace SequentSoft\ThreadFlow\Contracts;

use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;

interface BotManagerInterface
{
    public function getConfig(): ConfigInterface;

    public function getChannelConfig(string $channelName): ConfigInterface;

    public function on(string $event, callable $callback): void;

    public function getAvailableChannels(): array;

    public function channel(string $channelName): BotInterface;
}

<?php

namespace SequentSoft\ThreadFlow\Testing;

use Closure;
use SequentSoft\ThreadFlow\ThreadFlowBotManager;

class FakeBotManager extends ThreadFlowBotManager
{
    protected FakeChannelBot $lastChannelBot;

    public function channel(string $channelName): FakeChannelBot
    {
        return $this->lastChannelBot = new FakeChannelBot(
            parent::channel($channelName)
        );
    }

    public function assertSentOutgoingMessageCount(int $count): void
    {
        $this->lastChannelBot->assertSentOutgoingMessageCount($count);
    }

    public function assertSentOutgoingMessage(Closure $callback): void
    {
        $this->lastChannelBot->assertSentOutgoingMessage($callback);
    }

    public function assertDispatchedPageCount(int $count): void
    {
        $this->lastChannelBot->assertDispatchedPageCount($count);
    }

    public function assertDispatchedPage(Closure $callback): void
    {
        $this->lastChannelBot->assertDispatchedPage($callback);
    }

    public function assertDispatchedPageClass(string $pageClass): void
    {
        $this->lastChannelBot->assertDispatchedPageClass($pageClass);
    }

    public function assertNotingDispatched(): void
    {
        $this->lastChannelBot->assertNotingDispatched();
    }

    public function assertNotingSent(): void
    {
        $this->lastChannelBot->assertNotingSent();
    }

    public function assertSentTextMessage(string $text): void
    {
        $this->lastChannelBot->assertSentTextMessage($text);
    }

    public function assertCurrentPageClass(string $pageClass): void
    {
        $this->lastChannelBot->assertCurrentPageClass($pageClass);
    }

    public function assertCurrentPage(callable $callback): void
    {
        $this->lastChannelBot->assertCurrentPage($callback);
    }
}

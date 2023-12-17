<?php

namespace SequentSoft\ThreadFlow\Testing;

use Closure;
use SequentSoft\ThreadFlow\ChannelBot;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\ThreadFlowBotManager;

class FakeBotManager extends ThreadFlowBotManager
{
    protected ?ResultsRecorder $botResultsRecorder = null;

    protected function getResultsRecorder(): ResultsRecorder
    {
        if ($this->botResultsRecorder === null) {
            $this->botResultsRecorder = new ResultsRecorder();
        }

        return $this->botResultsRecorder;
    }

    protected function makeNewChannelBot(string $channelName): BotInterface
    {
        return new FakeChannelBot(
            $channelName,
            $this->getChannelConfig($channelName),
            $this->getSessionStore($channelName),
            $this->router,
            $this->getOutgoingChannel($channelName),
            $this->getIncomingChannel($channelName),
            $this->getDispatcher($channelName),
            $this->eventBus->makeChannelEventBus($channelName),
            $this->getResultsRecorder(),
        );
    }

    public function __call($name, $arguments)
    {
        if (str_starts_with($name, 'assert')) {
            return $this->getResultsRecorder()->$name(...$arguments);
        }

        throw new \BadMethodCallException("Method {$name} does not exist.");
    }
}

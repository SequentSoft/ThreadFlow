<?php

namespace SequentSoft\ThreadFlow\Contracts;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Channel\Incoming\IncomingChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Outgoing\OutgoingChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Config\SimpleConfigInterface;
use SequentSoft\ThreadFlow\Contracts\DataFetchers\DataFetcherInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface as IMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface as OMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Exceptions\Channel\ChannelNotConfiguredException;

interface BotInterface
{
    /**
     * @param MessageContextInterface|string $context
     * @param class-string<PageInterface> $pageClass
     * @param array $pageAttributes
     * @return void
     */
    public function showPage(
        MessageContextInterface|string $context,
        string $pageClass,
        array $pageAttributes = []
    ): void;

    public function sendMessage(
        MessageContextInterface|string $context,
        OMessageInterface|string $message
    ): OMessageInterface;

    public function handleProcessingExceptions(Closure $callback): void;

    public function getChannelName(): string;

    public function setDispatcher(DispatcherInterface $dispatcher): void;

    public function setIncomingChannel(IncomingChannelInterface $incomingChannel): void;

    public function setOutgoingChannel(OutgoingChannelInterface $outgoingChannel): void;

    /**
     * @param class-string<EventInterface> $event
     * @param callable $callback
     * @return void
     */
    public function on(string $event, callable $callback): void;

    /**
     * @param IMessageInterface $message
     * @param ?Closure(OMessageInterface, SessionInterface, PageInterface):OMessageInterface $outgoingCallback
     */
    public function process(IMessageInterface $message, ?Closure $outgoingCallback = null): void;

    /**
     * @param IMessageInterface $message
     */
    public function dispatch(IMessageInterface $message): void;

    public function listen(DataFetcherInterface $dataFetcher): void;

    public function getConfig(): SimpleConfigInterface;
}

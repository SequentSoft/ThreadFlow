<?php

namespace SequentSoft\ThreadFlow\Contracts;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\DataFetchers\DataFetcherInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface as IMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface as OMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Exceptions\Channel\ChannelNotConfiguredException;

interface BotInterface
{
    public function showPage(
        string $channelName,
        MessageContextInterface|string $context,
        string $pageClass,
        array $pageAttributes = []
    ): void;

    /**
     * @param class-string<PageInterface> $channelName
     * @param IMessageInterface $message
     * @param ?Closure(IMessageInterface, SessionInterface):IMessageInterface $incomingCallback
     * @param ?Closure(OMessageInterface, SessionInterface, PageInterface):OMessageInterface $outgoingCallback
     * @throws ChannelNotConfiguredException
     */
    public function process(
        string $channelName,
        IMessageInterface $message,
        ?Closure $incomingCallback = null,
        ?Closure $outgoingCallback = null,
    ): void;

    /**
     * @param class-string<PageInterface> $channelName
     * @param IMessageInterface $message
     * @param ?Closure(IMessageInterface, SessionInterface):IMessageInterface $incomingCallback
     * @param ?Closure(OMessageInterface, SessionInterface, PageInterface):OMessageInterface $outgoingCallback
     * @throws ChannelNotConfiguredException
     */
    public function dispatch(
        string $channelName,
        IMessageInterface $message,
        ?Closure $incomingCallback = null,
        ?Closure $outgoingCallback = null,
    ): void;

    /**
     * @param class-string<PageInterface> $channelName
     * @param IMessageInterface $message
     * @param ?Closure(IMessageInterface, SessionInterface):IMessageInterface $incomingCallback
     * @param ?Closure(OMessageInterface, SessionInterface, PageInterface):OMessageInterface $outgoingCallback
     */
    public function dispatchSync(
        string $channelName,
        IMessageInterface $message,
        ?Closure $incomingCallback = null,
        ?Closure $outgoingCallback = null,
    ): void;

    /**
     * @param string $channelName
     * @param DataFetcherInterface $dataFetcher
     * @param ?Closure(IMessageInterface):IMessageInterface $beforeDispatchCallback
     * @param ?Closure(OMessageInterface, SessionInterface, PageInterface):OMessageInterface $outgoingCallback
     * @return void
     * @throws ChannelNotConfiguredException
     */
    public function listen(
        string $channelName,
        DataFetcherInterface $dataFetcher,
        ?Closure $beforeDispatchCallback = null,
        ?Closure $outgoingCallback = null
    ): void;

    /**
     * @param string $channelName
     * @param DataFetcherInterface $dataFetcher
     * @param ?Closure(IMessageInterface):IMessageInterface $beforeDispatchCallback
     * @param ?Closure(OMessageInterface, SessionInterface, PageInterface):OMessageInterface $outgoingCallback
     * @return void
     * @throws ChannelNotConfiguredException
     */
    public function listenSync(
        string $channelName,
        DataFetcherInterface $dataFetcher,
        ?Closure $beforeDispatchCallback = null,
        ?Closure $outgoingCallback = null
    ): void;

    public function incoming(string $channelName, Closure $callback): void;

    public function outgoing(string $channelName, Closure $callback): void;

    public function getChannelConfig(string $channelName): ConfigInterface;

    public function getAvailableChannels(): array;
}

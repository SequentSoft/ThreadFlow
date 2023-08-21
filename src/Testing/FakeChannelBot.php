<?php

namespace SequentSoft\ThreadFlow\Testing;

use Closure;
use Exception;
use SequentSoft\ThreadFlow\Channel\Outgoing\CallbackOutgoingChannel;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Incoming\IncomingChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Channel\Outgoing\OutgoingChannelInterface;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\SimpleConfigInterface;
use SequentSoft\ThreadFlow\Contracts\DataFetchers\DataFetcherInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface as IMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface as OMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\TextOutgoingRegularMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Events\Page\PageDispatchedEvent;
use PHPUnit\Framework\Assert as PHPUnit;

class FakeChannelBot implements BotInterface
{
    protected array $sentOutgoingMessages = [];

    protected array $dispatchedPages = [];

    public function __construct(
        protected BotInterface $bot
    ) {
        $bot->on(PageDispatchedEvent::class, function (PageDispatchedEvent $event) {
            $this->dispatchedPages[] = $event->getPage();
        });

        $this->fakeOutgoingChannel();
    }

    public function showPage(
        string|MessageContextInterface $context,
        string $pageClass,
        array $pageAttributes = []
    ): void {
        $this->bot->showPage($context, $pageClass, $pageAttributes);
    }

    public function sendMessage(
        MessageContextInterface|string $context,
        OMessageInterface|string $message
    ): OMessageInterface {
        return $this->bot->sendMessage($context, $message);
    }

    public function getChannelName(): string
    {
        return $this->bot->getChannelName();
    }

    public function setDispatcher(DispatcherInterface $dispatcher): void
    {
        $this->bot->setDispatcher($dispatcher);
    }

    public function setIncomingChannel(IncomingChannelInterface $incomingChannel): void
    {
        $this->bot->setIncomingChannel($incomingChannel);
    }

    public function setOutgoingChannel(OutgoingChannelInterface $outgoingChannel): void
    {
        // do nothing
    }

    protected function fakeOutgoingChannel()
    {
        $this->bot->setOutgoingChannel(
            new CallbackOutgoingChannel(
                $this->getConfig(),
                function (OMessageInterface $message, SessionInterface $session, ?PageInterface $contextPage = null) {
                    $this->sentOutgoingMessages[] = $message;

                    // set random string as message id
                    return $message->setId(
                        bin2hex(random_bytes(16))
                    );
                }
            )
        );
    }

    public function on(string $event, callable $callback): void
    {
        $this->bot->on($event, $callback);
    }

    public function process(IMessageInterface $message, ?Closure $outgoingCallback = null): void
    {
        $this->bot->process(
            $message,
            function (OMessageInterface $message, SessionInterface $session, PageInterface $contextPage) {
                $this->sentOutgoingMessages[] = $message;
            }
        );
    }

    public function dispatch(IMessageInterface $message): void
    {
        $this->bot->dispatch($message);
    }

    public function listen(DataFetcherInterface $dataFetcher): void
    {
        $this->bot->listen($dataFetcher);
    }

    public function getConfig(): SimpleConfigInterface
    {
        return $this->bot->getConfig();
    }

    public function assertSentOutgoingMessageCount(int $count): void
    {
        PHPUnit::assertCount($count, $this->sentOutgoingMessages);
    }

    public function assertSentOutgoingMessage(Closure $callback): void
    {
        $exists = false;
        foreach ($this->sentOutgoingMessages as $message) {
            $exists = $callback($message);
        }
        PHPUnit::assertTrue($exists);
    }

    public function assertDispatchedPageCount(int $count): void
    {
        PHPUnit::assertCount($count, $this->dispatchedPages);
    }

    public function assertDispatchedPage(Closure $callback): void
    {
        $exists = false;
        foreach ($this->dispatchedPages as $page) {
            $exists = $callback($page);
        }
        PHPUnit::assertTrue($exists);
    }

    public function assertDispatchedPageClass(string $pageClass): void
    {
        $this->assertDispatchedPage(function (PageInterface $page) use ($pageClass) {
            return $page instanceof $pageClass;
        });
    }

    public function assertNotingDispatched(): void
    {
        $this->assertDispatchedPageCount(0);
    }

    public function assertNotingSent(): void
    {
        $this->assertSentOutgoingMessageCount(0);
    }

    public function assertCurrentPageClass(string $pageClass): void
    {
        $page = $this->dispatchedPages[count($this->dispatchedPages) - 1];
        PHPUnit::assertInstanceOf($pageClass, $page);
    }

    public function assertCurrentPage(callable $callback): void
    {
        $page = $this->dispatchedPages[count($this->dispatchedPages) - 1];
        PHPUnit::assertTrue($callback($page));
    }

    public function assertSentTextMessage(string $text): void
    {
        $this->assertSentOutgoingMessage(function (OMessageInterface $message) use ($text) {
            if ($message instanceof TextOutgoingRegularMessageInterface) {
                return $message->getText() === $text;
            }

            return false;
        });
    }
}

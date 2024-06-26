<?php

// namespace "Illuminate\Testing" added to force nunomaduro/collision to ignore this file

namespace SequentSoft\ThreadFlow\Testing\Illuminate\Testing;

use Closure;
use PHPUnit\Framework\Assert as PHPUnit;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Service\IncomingServiceMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\BasicOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\TextOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Contracts\Testing\ResultsRecorderInterface;

class ResultsRecorder implements ResultsRecorderInterface
{
    protected array $sentOutgoingMessages = [];

    protected array $dispatchedPages = [];

    protected array $dispatchedPagesMethods = [];

    public function recordSentOutgoingMessage(BasicOutgoingMessageInterface $message): static
    {
        $this->sentOutgoingMessages[] = $message;

        return $this;
    }

    public function recordPageDispatchedWithoutHandler(
        PageInterface $page,
    ): static {
        $this->dispatchedPages[] = $page;
        $this->dispatchedPagesMethods[] = null;

        return $this;
    }

    public function recordPageHandleRegularMessage(
        PageInterface $page,
        IncomingMessageInterface $message
    ): static {
        $this->dispatchedPages[] = $page;
        $this->dispatchedPagesMethods[] = 'handleRegularMessage';

        return $this;
    }

    public function recordPageHandleServiceMessage(
        PageInterface $page,
        IncomingServiceMessageInterface $message
    ): static {
        $this->dispatchedPages[] = $page;
        $this->dispatchedPagesMethods[] = 'handleServiceMessage';

        return $this;
    }

    public function recordPageHandleWelcomeMessage(
        PageInterface $page,
        IncomingServiceMessageInterface $message
    ): static {
        $this->dispatchedPages[] = $page;
        $this->dispatchedPagesMethods[] = 'welcome';

        return $this;
    }

    public function recordPageShow(PageInterface $page): static
    {
        $this->dispatchedPages[] = $page;
        $this->dispatchedPagesMethods[] = 'show';

        return $this;
    }

    public function getOutgoingMessage(?int $index = null): ?BasicOutgoingMessageInterface
    {
        return $index !== null
            ? $this->sentOutgoingMessages[$index] ?? null
            : $this->sentOutgoingMessages[count($this->sentOutgoingMessages) - 1] ?? null;
    }

    public function getDispatchedPage(?int $index = null): ?PageInterface
    {
        return $index !== null
            ? $this->dispatchedPages[$index] ?? null
            : $this->dispatchedPages[count($this->dispatchedPages) - 1] ?? null;
    }

    public function getDispatchedPageMethod(?int $index = null): ?string
    {
        return $index !== null
            ? $this->dispatchedPagesMethods[$index] ?? null
            : $this->dispatchedPagesMethods[count($this->dispatchedPagesMethods) - 1] ?? null;
    }

    public function add(ResultsRecorder $recorder): static
    {
        $this->sentOutgoingMessages = array_merge(
            $this->sentOutgoingMessages,
            $recorder->sentOutgoingMessages
        );

        $this->dispatchedPages = array_merge(
            $this->dispatchedPages,
            $recorder->dispatchedPages
        );

        return $this;
    }

    public function assertState(
        string $pageClass,
        ?string $method = null,
        ?array $attributes = null,
        ?int $index = null
    ): static {
        $latestPage = $this->getDispatchedPage($index);

        if ($latestPage === null) {
            if ($index !== null) {
                PHPUnit::fail("Page with index {$index} not found");
            }

            PHPUnit::fail('No pages dispatched');
        }

        PHPUnit::assertEquals($pageClass, $latestPage::class, 'Dispatched page mismatch');

        if ($method !== null) {
            PHPUnit::assertEquals($method, $this->getDispatchedPageMethod($index));
        }

        if ($attributes !== null) {
            PHPUnit::assertEquals($attributes, $latestPage->getAttributes());
        }

        return $this;
    }

    public function assertStatesChain(array $states): static
    {
        foreach ($states as $index => $state) {
            $this->assertState(
                $state[0],
                $state[1] ?? null,
                $state[2] ?? null,
                $index
            );
        }

        return $this;
    }

    public function assertOutgoingMessagesCount(int $count): static
    {
        PHPUnit::assertCount($count, $this->sentOutgoingMessages, 'Outgoing messages count mismatch');

        return $this;
    }

    public function assertOutgoingMessage(Closure $callback, ?int $index = null): static
    {
        $latestMessage = $this->getOutgoingMessage($index);

        if ($latestMessage === null) {
            PHPUnit::fail(is_null($index) ? 'Latest message not found' : "Message with index {$index} not found");
        }

        PHPUnit::assertTrue($callback($latestMessage), 'Outgoing message mismatch');

        return $this;
    }

    public function assertDispatchedPagesCount(int $count): static
    {
        PHPUnit::assertCount($count, $this->dispatchedPages, 'Dispatched pages count mismatch');

        return $this;
    }

    public function assertDispatchedPage(Closure $callback, ?int $index = null): static
    {
        $latestPage = $this->getDispatchedPage($index);

        if ($latestPage === null) {
            PHPUnit::fail(is_null($index) ? 'Latest page not found' : "Page with index {$index} not found");
        }

        PHPUnit::assertTrue($callback($latestPage), 'Dispatched page mismatch');

        return $this;
    }

    public function assertOutgoingMessageText(string $text, ?int $index = null): static
    {
        $latestMessage = $this->getOutgoingMessage($index);

        if ($latestMessage === null) {
            PHPUnit::fail(is_null($index) ? 'Latest message not found' : "Message with index {$index} not found");
        }

        if (! $latestMessage instanceof TextOutgoingMessageInterface) {
            PHPUnit::fail(
                is_null($index)
                    ? 'Latest message is not a text message'
                    : "Message with index {$index} is not a text message"
            );
        }

        PHPUnit::assertEquals($text, $latestMessage->getText());

        return $this;
    }

    public function assertOutgoingMessageTextContains(string $text, ?int $index = null): static
    {
        $latestMessage = $this->getOutgoingMessage($index);

        if ($latestMessage === null) {
            PHPUnit::fail(is_null($index) ? 'Latest message not found' : "Message with index {$index} not found");
        }

        if (! $latestMessage instanceof TextOutgoingMessageInterface) {
            PHPUnit::fail(
                is_null($index)
                    ? 'Latest message is not a text message'
                    : "Message with index {$index} is not a text message"
            );
        }

        PHPUnit::assertStringContainsString($text, $latestMessage->getText());

        return $this;
    }
}

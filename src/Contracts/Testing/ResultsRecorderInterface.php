<?php

namespace SequentSoft\ThreadFlow\Contracts\Testing;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Service\IncomingServiceMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\CommonOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;

interface ResultsRecorderInterface
{
    public function recordPageDispatchedWithoutHandler(
        PageInterface $page,
    ): static;

    public function recordSentOutgoingMessage(CommonOutgoingMessageInterface $message): static;

    public function recordPageHandleRegularMessage(
        PageInterface $page,
        IncomingMessageInterface $message
    ): static;

    public function recordPageHandleServiceMessage(
        PageInterface $page,
        IncomingServiceMessageInterface $message
    ): static;

    public function recordPageHandleWelcomeMessage(
        PageInterface $page,
        IncomingServiceMessageInterface $message
    ): static;

    public function recordPageShow(PageInterface $page): static;

    public function getOutgoingMessage(?int $index = null): ?CommonOutgoingMessageInterface;

    public function getDispatchedPage(?int $index = null): ?PageInterface;

    public function getDispatchedPageMethod(?int $index = null): ?string;

    public function assertState(
        string $pageClass,
        ?string $method = null,
        ?array $attributes = null,
        ?int $index = null
    ): static;

    public function assertStatesChain(array $states): static;

    public function assertOutgoingMessagesCount(int $count): static;

    public function assertOutgoingMessage(Closure $callback, ?int $index = null): static;

    public function assertDispatchedPagesCount(int $count): static;

    public function assertDispatchedPage(Closure $callback, ?int $index = null): static;

    public function assertOutgoingMessageText(string $text, ?int $index = null): static;

    public function assertOutgoingMessageTextContains(string $text, ?int $index = null): static;
}

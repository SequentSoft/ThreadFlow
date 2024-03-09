<?php

namespace SequentSoft\ThreadFlow\Contracts\Page;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\CommonIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

interface PageInterface
{
    public function isDontDisturb(): bool;

    public function isTrackingPrev(): bool;

    public function getId(): string;

    public function setSession(SessionInterface $session): static;

    public function setContext(MessageContextInterface $messageContext): static;

    public function setPrev(?PageInterface $prev): static;

    public function getPrev(): ?PageInterface;

    public function getChannelName(): string;

    public function getContext(): MessageContextInterface;

    public function isBackground(): bool;

    public function execute(
        EventBusInterface $eventBus,
        ?CommonIncomingMessageInterface $message,
        Closure $callback
    ): mixed;
}

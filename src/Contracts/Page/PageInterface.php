<?php

namespace SequentSoft\ThreadFlow\Contracts\Page;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\SimpleKeyboardInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\BasicIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

interface PageInterface
{
    public function isDontDisturb(): bool;

    public function keepPrevPageReferenceAfterTransition(): bool;

    public function getId(): string;

    public function setActivePagesRepository(ActivePagesRepositoryInterface $activePagesRepository): static;

    public function getSessionId(): string;

    public function setSession(SessionInterface $session): static;

    public function setContext(MessageContextInterface $messageContext): static;

    public function setPrevPageId(?string $prevId): static;

    public function getPrevPageId(): ?string;

    public function resolvePrevPage(): ?PageInterface;

    public function getLastKeyboard(): ?SimpleKeyboardInterface;

    public function getChannelName(): string;

    public function getContext(): MessageContextInterface;

    public function isBackground(): bool;

    public function getAttributes(): array;

    public function autoSetPrevPageReference(): bool;

    public function execute(
        EventBusInterface $eventBus,
        ?BasicIncomingMessageInterface $message,
        Closure $callback
    ): mixed;
}

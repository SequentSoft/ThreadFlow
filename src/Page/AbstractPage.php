<?php

namespace SequentSoft\ThreadFlow\Page;

use Closure;
use ReflectionMethod;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\IncomingRegularMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Service\IncomingServiceMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\OutgoingRegularMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Router\RouterInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Messages\Outgoing\Regular\OutgoingRegularMessage;

abstract class AbstractPage
{
    protected Closure $outgoingCallback;

    protected array $pageEvents = [];

    protected array $breadcrumbs = [];

    public function __construct(
        protected array $attributes,
        protected SessionInterface $session,
        protected IncomingMessageInterface $message,
        protected RouterInterface $router,
    ) {
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setBreadcrumbs(array $breadcrumbs): static
    {
        $this->breadcrumbs = $breadcrumbs;

        return $this;
    }

    public function back(?string $fallbackPageClass = null, array $fallbackPageAttributes = []): ?PendingDispatchPage
    {
        $breadcrumbs = $this->breadcrumbs;
        $latestBreadcrumb = array_slice($breadcrumbs, -1)[0] ?? null;
        $this->breadcrumbs = array_slice($breadcrumbs, 0, -1);

        if ($latestBreadcrumb) {
            return $this->next(
                $latestBreadcrumb->getPageClass(),
                $latestBreadcrumb->getAttributes(),
            )->withBreadcrumbsReplace();
        }

        if ($fallbackPageClass) {
            return $this->next($fallbackPageClass, $fallbackPageAttributes);
        }

        return null;
    }

    protected function next(string $pageClass, array $attributes = []): PendingDispatchPage
    {
        $pendingDispatchPage = new PendingDispatchPage(
            $pageClass,
            $attributes,
            $this->session,
            $this->message,
            $this->router,
        );

        $pendingDispatchPage->setBreadcrumbs($this->breadcrumbs);

        return $pendingDispatchPage;
    }

    public function execute(Closure $callback): ?PendingDispatchPage
    {
        $this->outgoingCallback = $callback;

        $isEntering = $this->router->getCurrentPage(
            $this->message,
            $this->session,
            ''
        )->getPageClass() !== static::class;

        $this->router->setCurrentPage(
            $this->session,
            static::class,
            $this->attributes,
            $this->breadcrumbs,
        );

        $result = $this->handleIncoming($isEntering);

        $this->router->setCurrentPage(
            $this->session,
            static::class,
            $this->attributes,
            $this->breadcrumbs,
        );

        return $result;
    }

    protected function handleIncoming(bool $isEntering)
    {
        if ($isEntering) {
            return $this->executeShowHandler();
        }

        if ($this->message instanceof IncomingRegularMessageInterface) {
            return $this->executeRegularMessageHandler($this->message);
        }

        if ($this->message instanceof IncomingServiceMessageInterface) {
            return $this->executeServiceMessageHandler($this->message);
        }
    }

    protected function executeShowHandler(): ?PendingDispatchPage
    {
        if (method_exists($this, 'show')) {
            return $this->show();
        }

        return null;
    }

    protected function executeRegularMessageHandler(IncomingRegularMessageInterface $message): ?PendingDispatchPage
    {
        if (method_exists($this, 'handleMessage')) {
            return $this->handleMessage($message);
        }

        return null;
    }

    protected function executeServiceMessageHandler(IncomingServiceMessageInterface $message): ?PendingDispatchPage
    {
        if (method_exists($this, 'handleServiceMessage')) {
            return $this->handleServiceMessage($message);
        }

        return null;
    }

    public function on(string $eventName, Closure $callback): static
    {
        $this->pageEvents[$eventName][] = $callback;

        return $this;
    }

    protected function setAttribute(string $key, mixed $value): static
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    protected function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    protected function reply(OutgoingRegularMessageInterface $message): OutgoingRegularMessageInterface
    {
        if (! $message->getContext()) {
            $message->setContext($this->message->getContext());
        }

        return call_user_func($this->outgoingCallback, $message);
    }

    protected function embed(string $pageClass, array $attributes = [])
    {
        return new PendingDispatchEmbedPage(
            $pageClass,
            $attributes,
            $this->session,
            $this->message,
            $this->router,
        );
    }

    protected function emit(string $event, mixed $data): void
    {
        foreach ($this->pageEvents[$event] ?? [] as $callback) {
            $callback($data);
        }
    }
}

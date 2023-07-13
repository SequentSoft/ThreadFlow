<?php

namespace SequentSoft\ThreadFlow\Page;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular\IncomingRegularMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Service\IncomingServiceMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Router\RouterInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Messages\Outgoing\Regular\OutgoingRegularMessage;

abstract class AbstractPage
{
    protected Closure $outgoingCallback;

    protected array $pageEvents = [];

    public function __construct(
        protected array $attributes,
        protected SessionInterface $session,
        protected IncomingMessageInterface $message,
        protected RouterInterface $router,
    ) {}

    public function execute(Closure $callback)
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
        );

        $result = $this->handleIncoming($isEntering);

        $this->router->setCurrentPage(
            $this->session,
            static::class,
            $this->attributes,
        );

        return $result;
    }

    protected function handleIncoming(bool $isEntering)
    {
        if ($isEntering) {
            return $this->show();
        }

        if ($this->message instanceof IncomingRegularMessageInterface) {
            return $this->handleMessage($this->message);
        }

        if ($this->message instanceof IncomingServiceMessageInterface) {
            return $this->handleServiceMessage($this->message);
        }
    }

    public function on(string $eventName, Closure $callback): static
    {
        $this->pageEvents[$eventName][] = $callback;

        return $this;
    }

    protected function show()
    {
        //
    }

    protected function handleMessage(IncomingRegularMessageInterface $message)
    {
        //
    }

    protected function handleServiceMessage(IncomingServiceMessageInterface $message)
    {

    }

    protected function reply(OutgoingRegularMessage $message): OutgoingRegularMessage
    {
        if (! $message->getContext()) {
            $message->setContext($this->message->getContext());
        }

        return call_user_func($this->outgoingCallback, $message);
    }

    protected function next(string $pageClass, array $attributes = []): PendingDispatchPage
    {
        return new PendingDispatchPage(
            $pageClass,
            $attributes,
            $this->session,
            $this->message,
            $this->router,
        );
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

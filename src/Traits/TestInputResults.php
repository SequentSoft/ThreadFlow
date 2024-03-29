<?php

namespace SequentSoft\ThreadFlow\Traits;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Events\EventBusInterface;
use SequentSoft\ThreadFlow\Contracts\Testing\ResultsRecorderInterface;
use SequentSoft\ThreadFlow\Events\Message\OutgoingMessageSentEvent;
use SequentSoft\ThreadFlow\Events\Page\PageHandleRegularMessageEvent;
use SequentSoft\ThreadFlow\Events\Page\PageHandleServiceMessageEvent;
use SequentSoft\ThreadFlow\Events\Page\PageHandleWelcomeMessageEvent;
use SequentSoft\ThreadFlow\Events\Page\PageHasNoMessageHandlerEvent;
use SequentSoft\ThreadFlow\Events\Page\PageShowEvent;
use SequentSoft\ThreadFlow\Testing\Illuminate\Testing\ResultsRecorder;

trait TestInputResults
{
    /**
     * Register listeners to capture test input results.
     */
    private function registerTestInputResultListeners(
        EventBusInterface $eventBus,
        ResultsRecorderInterface $resultsRecorder
    ): void {
        $eventBus->listen(
            PageHandleRegularMessageEvent::class,
            fn (string $name, PageHandleRegularMessageEvent $event) => $resultsRecorder->recordPageHandleRegularMessage(
                $event->getPage(),
                $event->getMessage()
            )
        );

        $eventBus->listen(
            PageHandleServiceMessageEvent::class,
            fn (string $name, PageHandleServiceMessageEvent $event) => $resultsRecorder->recordPageHandleServiceMessage(
                $event->getPage(),
                $event->getMessage()
            )
        );

        $eventBus->listen(
            PageHandleWelcomeMessageEvent::class,
            fn (string $name, PageHandleWelcomeMessageEvent $event) => $resultsRecorder->recordPageHandleWelcomeMessage(
                $event->getPage(),
                $event->getMessage()
            )
        );

        $eventBus->listen(
            PageHasNoMessageHandlerEvent::class,
            fn (string $name, PageHasNoMessageHandlerEvent $event) => $resultsRecorder->recordPageDispatchedWithoutHandler($event->getPage())
        );

        $eventBus->listen(
            PageShowEvent::class,
            fn (string $name, PageShowEvent $event) => $resultsRecorder->recordPageShow($event->getPage())
        );

        $eventBus->listen(
            OutgoingMessageSentEvent::class,
            fn (string $name, OutgoingMessageSentEvent $event) => $resultsRecorder->recordSentOutgoingMessage(
                $event->getMessage()
            )
        );
    }

    /**
     * Capture test input results.
     * The event bus listeners will be temporarily replaced with the test input result listeners.
     */
    protected function captureTestInputResults(EventBusInterface $eventBus, Closure $callback): ResultsRecorder
    {
        $originalListeners = $eventBus->getListeners();

        $resultsRecorder = new ResultsRecorder();

        $this->registerTestInputResultListeners($eventBus, $resultsRecorder);

        $callback();

        $eventBus->setListeners($originalListeners);

        return $resultsRecorder;
    }
}

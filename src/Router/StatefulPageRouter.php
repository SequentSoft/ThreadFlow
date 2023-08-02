<?php

namespace SequentSoft\ThreadFlow\Router;

use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Router\RouterInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class StatefulPageRouter implements RouterInterface
{
    public function getCurrentPageState(
        IncomingMessageInterface $message,
        SessionInterface $session,
        string $fallbackClass
    ): PageStateInterface {
        $stateId = $message->getStateId();

        if ($stateId) {
            $backgroundPageState = $session->getBackgroundPageStates()->get($stateId);

            if ($backgroundPageState) {
                return $backgroundPageState;
            }
        }

        $state = $session->getPageState();

        if (is_null($state->getPageClass())) {
            $state->setPageClass($fallbackClass);
        }

        return $state;
    }

    public function setCurrentPageState(
        SessionInterface $session,
        PageStateInterface $pageState,
    ): void {
        $session->setPageState($pageState);
    }
}

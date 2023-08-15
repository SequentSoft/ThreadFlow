<?php

namespace SequentSoft\ThreadFlow\Messages\Outgoing;

use Exception;
use RuntimeException;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Messages\Message;

abstract class OutgoingMessage extends Message implements OutgoingMessageInterface
{
    private function getContextPage(): ?PageInterface
    {
        $debug = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2];
        $page = $debug['object'] ?? null;

        if (!$page instanceof PageInterface) {
            throw new RuntimeException('Context page needed for this action');
        }

        return $page;
    }

    public function reply(): static
    {
        return (function (OutgoingMessageInterface $message) {
            if (! method_exists($this, 'reply')) {
                throw new RuntimeException('Method reply() not implemented');
            }

            return $this->reply($message);
        })->call($this->getContextPage(), $this);
    }

    public function update(): static
    {
        return (function (OutgoingMessageInterface $message) {
            if (! method_exists($this, 'updateMessage')) {
                throw new RuntimeException('Method updateMessage() not implemented');
            }

            return $this->updateMessage($message);
        })->call($this->getContextPage(), $this);
    }
}

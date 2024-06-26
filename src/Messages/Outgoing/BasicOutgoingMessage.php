<?php

namespace SequentSoft\ThreadFlow\Messages\Outgoing;

use RuntimeException;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\BasicOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;
use SequentSoft\ThreadFlow\Messages\Message;

abstract class BasicOutgoingMessage extends Message implements BasicOutgoingMessageInterface
{
    private function getContextPage(): PageInterface
    {
        $debug = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS, 10);

        foreach ($debug as $value) {
            $object = $value['object'] ?? null;

            if ($object instanceof PageInterface) {
                return $object;
            }
        }

        throw new RuntimeException('Context page needed for this action');
    }

    public function reply(): static
    {
        return (function (BasicOutgoingMessageInterface $message) {
            if (! method_exists($this, 'reply')) {
                throw new RuntimeException('Method reply() not implemented');
            }

            return $this->reply($message);
        })->call($this->getContextPage(), $this);
    }

    public function update(): static
    {
        return (function (BasicOutgoingMessageInterface $message) {
            if (! method_exists($this, 'updateMessage')) {
                throw new RuntimeException('Method updateMessage() not implemented');
            }

            return $this->updateMessage($message);
        })->call($this->getContextPage(), $this);
    }
}

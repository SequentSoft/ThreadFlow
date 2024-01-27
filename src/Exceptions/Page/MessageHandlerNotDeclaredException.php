<?php

namespace SequentSoft\ThreadFlow\Exceptions\Page;

use Exception;
use SequentSoft\ThreadFlow\Contracts\Page\PageInterface;

class MessageHandlerNotDeclaredException extends Exception
{
    public const TYPE_ANSWER = 'answer';

    public const TYPE_SERVICE = 'service';

    public const TYPE_SHOW = 'show';

    public function __construct(protected string $handlerType, protected PageInterface $page)
    {
        parent::__construct("Message handler for {$handlerType} is not declared in " . get_class($page) . '.');
    }

    public function getHandlerType(): string
    {
        return $this->handlerType;
    }

    public function getPage(): PageInterface
    {
        return $this->page;
    }
}

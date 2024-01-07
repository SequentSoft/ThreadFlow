<?php

namespace SequentSoft\ThreadFlow\Contracts\Session;

use Closure;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;

interface SessionStoreInterface
{
    public function useSession(MessageContextInterface $context, Closure $callback): mixed;
}

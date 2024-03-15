<?php

namespace SequentSoft\ThreadFlow\Contracts\Session;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;

interface SessionStoreInterface
{
    public function useSession(MessageContextInterface $context, callable $callback): mixed;
}

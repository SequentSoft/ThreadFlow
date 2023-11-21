<?php

namespace SequentSoft\ThreadFlow\Contracts\Session;

use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;

interface SessionStoreInterface
{
    public function new(MessageContextInterface $context): SessionInterface;

    public function load(MessageContextInterface $context): SessionInterface;

    public function save(MessageContextInterface $context, SessionInterface $session): void;
}

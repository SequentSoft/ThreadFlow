<?php

namespace SequentSoft\ThreadFlow\Exceptions\Session;

use Exception;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class SessionSizeLimitExceededException extends Exception
{
    public function __construct(
        protected SessionInterface $session,
        protected int $size,
        protected int $limit,
    ) {
        parent::__construct(
            sprintf(
                'Session size limit exceeded. Current size: %d KB, limit: %d KB.',
                round($size / 1024, 2),
                round($limit / 1024, 2),
            ),
        );
    }

    public function getSession(): SessionInterface
    {
        return $this->session;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}

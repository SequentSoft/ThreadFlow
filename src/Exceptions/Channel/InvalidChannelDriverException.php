<?php

namespace SequentSoft\ThreadFlow\Exceptions\Channel;

use Exception;

class InvalidChannelDriverException extends Exception
{
    public function __construct(string $message = "", int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Service;

interface PollUpdateIncomingMessageInterface extends IncomingServiceMessageInterface
{
    public function getPollId(): string;

    public function getPollOption(): string;

    public function getPollVoteCount(): int;
}

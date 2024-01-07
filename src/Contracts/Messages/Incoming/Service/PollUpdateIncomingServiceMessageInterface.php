<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Service;

interface PollUpdateIncomingServiceMessageInterface extends IncomingServiceMessageInterface
{
    public function getPollId(): string;

    public function getPollOption(): string;

    public function getPollVoteCount(): int;
}

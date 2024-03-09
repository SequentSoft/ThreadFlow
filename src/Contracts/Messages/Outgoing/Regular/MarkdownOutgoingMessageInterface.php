<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular;

interface MarkdownOutgoingMessageInterface extends OutgoingMessageInterface
{
    public function getMarkdown(): string;
}

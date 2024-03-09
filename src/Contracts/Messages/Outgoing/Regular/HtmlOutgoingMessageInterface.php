<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular;

interface HtmlOutgoingMessageInterface extends OutgoingMessageInterface
{
    public function getHtml(): string;
}

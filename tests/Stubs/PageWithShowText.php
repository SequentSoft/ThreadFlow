<?php

namespace Test;

use SequentSoft\ThreadFlow\Messages\Outgoing\Regular\TextOutgoingRegularMessage;

class PageWithShowText extends \SequentSoft\ThreadFlow\Page\AbstractPage
{
    public function show()
    {
        $this->reply(
            TextOutgoingRegularMessage::make(
                'Hello world!'
            )
        );
    }
}

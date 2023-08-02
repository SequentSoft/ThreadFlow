<?php

namespace Tests\Stubs;

use SequentSoft\ThreadFlow\Messages\Outgoing\Regular\TextOutgoingRegularMessage;
use SequentSoft\ThreadFlow\Page\AbstractPage;

class AnswerPage extends AbstractPage
{
    public function show()
    {
        $this->reply(
            TextOutgoingRegularMessage::make('Hello', [
                ['login' => 'Login']
            ])
        );
    }

    public function handleMessage()
    {
        $this->reply(
            TextOutgoingRegularMessage::make('Hello', [
                ['login' => 'Login']
            ])
        );
    }
}

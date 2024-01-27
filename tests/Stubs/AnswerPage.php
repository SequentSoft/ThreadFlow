<?php

namespace Tests\Stubs;

use SequentSoft\ThreadFlow\Messages\Outgoing\Regular\TextOutgoingRegularMessage;
use SequentSoft\ThreadFlow\Page\AbstractPage;

class AnswerPage extends AbstractPage
{
    public function show()
    {
        TextOutgoingRegularMessage::make('Hello', [
            ['login' => 'Login'],
        ])->reply();
    }

    public function answer()
    {
        TextOutgoingRegularMessage::make('Hello', [
            ['login' => 'Login'],
        ])->reply();
    }
}

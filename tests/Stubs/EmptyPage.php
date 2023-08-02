<?php

namespace Tests\Stubs;

use SequentSoft\ThreadFlow\Page\AbstractPage;

class EmptyPage extends AbstractPage
{
    public function show()
    {
    }

    public function handleMessage()
    {
    }

    public function handleServiceMessage()
    {
    }
}

<?php

namespace Tests\Stubs;

use SequentSoft\ThreadFlow\Page\AbstractPage;

class WithAttributePage extends AbstractPage
{
    public function __construct(protected string $foo)
    {
    }

    public function show()
    {
    }

    public function answer()
    {
    }

    public function service()
    {
    }
}

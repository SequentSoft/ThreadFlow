<?php

namespace SequentSoft\ThreadFlow\Laravel\Page;

use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use Illuminate\Support\Facades\App;

class Page extends \SequentSoft\ThreadFlow\Page\AbstractPage
{
    protected function callHandlerMethod(string $method, ?IncomingMessageInterface $message): mixed
    {
        return App::call([$this, $method], [
            'message' => $message,
        ]);
    }
}

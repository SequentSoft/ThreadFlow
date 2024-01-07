<?php

namespace SequentSoft\ThreadFlow\Enums\State;

enum BreadcrumbsType: string
{
    case None = 'none';
    case Replace = 'replace';
    case Append = 'append';
}

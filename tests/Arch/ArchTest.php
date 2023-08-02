<?php

test('Dont use dd or dump')
    ->expect(['dd', 'dump'])
    ->not->toBeUsed();


test('Contracts dir could contain only interfaces')
    ->expect('SequentSoft\ThreadFlow\Contracts')
    ->toBeInterfaces();

test('Contracts should be suffixed with Interface')
    ->expect('SequentSoft\ThreadFlow\Contracts')
    ->toHaveSuffix('Interface');

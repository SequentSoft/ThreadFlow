<?php

use SequentSoft\ThreadFlow\Config;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Exceptions\Config\InvalidNestedConfigException;

it('can be created', function () {
    $config = new Config([
        //
    ]);

    expect($config)->toBeInstanceOf(ConfigInterface::class);
});

it('all values can be retrieved', function () {
    $config = new Config([
        'value-key' => 'value',
    ]);

    expect($config->all())
        ->toBeArray()
        ->toEqual([
            'value-key' => 'value',
        ]);
});

it('value can be retrieved', function () {
    $config = new Config([
        'value-key' => 'value',
    ]);

    expect($config->get('value-key'))
        ->toBeString()
        ->toEqual('value');
});

it('value can be retrieved with default', function () {
    $config = new Config([
        //
    ]);

    expect($config->get('value-key', 1))
        ->toBeInt()
        ->toEqual(1);
});

it('nested config can be retrieved', function () {
    $config = new Config([
        'nested' => [
            'value-key' => 'value',
        ],
    ]);

    expect($config->getNested('nested'))
        ->toBeInstanceOf(ConfigInterface::class);
});

it('nested config value can be retrieved', function () {
    $config = new Config([
        'nested' => [
            'value-key' => 'value',
        ],
    ]);

    expect($config->getNested('nested')->get('value-key'))
        ->toBeString()
        ->toEqual('value');
});

it('throws exception when nested config is invalid', function () {
    $config = new Config([
        'nested' => 'value',
    ]);

    $config->getNested('nested');
})->throws(InvalidNestedConfigException::class, 'Invalid nested config: nested');

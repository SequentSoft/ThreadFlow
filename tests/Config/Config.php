<?php

use SequentSoft\ThreadFlow\Config;
use SequentSoft\ThreadFlow\Exceptions\Config\InvalidNestedConfigException;

it('checks if the config is empty', function () {
    $config = new Config([]);
    expect($config->isEmpty())->toBeTrue();

    $config = new Config(['key' => 'value']);
    expect($config->isEmpty())->toBeFalse();
});

it('gets a config value by its key', function () {
    $config = new Config(['key' => 'value']);
    expect($config->get('key'))->toBe('value');
    expect($config->get('non_existent_key'))->toBeNull();
    expect($config->get('non_existent_key', 'default'))->toBe('default');
});

it('gets all config values', function () {
    $data = ['key1' => 'value1', 'key2' => 'value2'];
    $config = new Config($data);
    expect($config->all())->toBe($data);
});

it('merges with another config', function () {
    $config1 = new Config(['key1' => 'value1', 'key2' => 'value2']);
    $config2 = new Config(['key2' => 'new_value', 'key3' => 'value3']);
    $config1->merge($config2);

    expect($config1->all())->toBe([
        'key1' => 'value1',
        'key2' => 'new_value',
        'key3' => 'value3',
    ]);
});

it('gets a nested config', function () {
    $config = new Config(['key' => ['nested_key' => 'nested_value']]);
    $nestedConfig = $config->getNested('key');

    expect($nestedConfig)->toBeInstanceOf(Config::class);
    expect($nestedConfig->get('nested_key'))->toBe('nested_value');
});

it('throws an exception if the nested config is not an array', function () {
    $config = new Config(['key' => 'value']);
    $config->getNested('key');
})->throws(InvalidNestedConfigException::class, 'Invalid nested config: key');

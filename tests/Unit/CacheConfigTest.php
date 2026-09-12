<?php

use STDW\Cache\CacheConfig;

test('default handler is file', function () {
    $config = new CacheConfig([]);

    expect($config->handler())->toBe('file');
});

test('default storage is sys temp dir', function () {
    $config = new CacheConfig([]);

    expect($config->storage())->toBe(sys_get_temp_dir());
});

test('invalid handler falls back to file', function () {
    $config = new CacheConfig(['handler' => 'redis']);

    expect($config->handler())->toBe('file');
});

test('handler sqlite is accepted', function () {
    $config = new CacheConfig(['handler' => 'sqlite']);

    expect($config->handler())->toBe('sqlite');
});

test('handler file is accepted', function () {
    $config = new CacheConfig(['handler' => 'file']);

    expect($config->handler())->toBe('file');
});

test('valid storage path is kept', function () {
    $config = new CacheConfig(['storage' => $this->testStorage]);

    expect($config->storage())->toBe($this->testStorage);
});

test('invalid storage falls back to temp dir', function () {
    $config = new CacheConfig(['storage' => '/nonexistent/path']);

    expect($config->storage())->toBe(sys_get_temp_dir());
});

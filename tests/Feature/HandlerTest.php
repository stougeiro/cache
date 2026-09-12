<?php

use STDW\Cache\Cache;
use STDW\Cache\CacheConfig;
use STDW\Cache\Handler\FileCacheHandler;
use STDW\Cache\Handler\SqliteCacheHandler;

dataset('handlers', function () {
    $tmpFile = sys_get_temp_dir() . '/cache_test_file_' . uniqid('', true);
    $tmpSqlite = sys_get_temp_dir() . '/cache_test_sqlite_' . uniqid('', true);

    mkdir($tmpFile, 0755, true);
    mkdir($tmpSqlite, 0755, true);

    return [
        'file'   => [new FileCacheHandler($tmpFile)],
        'sqlite' => [new SqliteCacheHandler($tmpSqlite)],
    ];
});

dataset('caches', function () {
    $tmpFile = sys_get_temp_dir() . '/cache_test_file_' . uniqid('', true);
    $tmpSqlite = sys_get_temp_dir() . '/cache_test_sqlite_' . uniqid('', true);

    mkdir($tmpFile, 0755, true);
    mkdir($tmpSqlite, 0755, true);

    return [
        'file'   => [new Cache(new CacheConfig(['handler' => 'file', 'storage' => $tmpFile]))],
        'sqlite' => [new Cache(new CacheConfig(['handler' => 'sqlite', 'storage' => $tmpSqlite]))],
    ];
});

test('set and get round-trip', function ($handler) {
    $handler->set('key', 'value');

    expect($handler->get('key'))->toBe('value');
})->with('handlers');

test('has returns true for existing key', function ($handler) {
    $handler->set('key', 'value');

    expect($handler->has('key'))->toBeTrue();
})->with('handlers');

test('has returns false for nonexistent key', function ($handler) {
    expect($handler->has('nonexistent'))->toBeFalse();
})->with('handlers');

test('get returns default for nonexistent key', function ($handler) {
    expect($handler->get('nonexistent', 'fallback'))->toBe('fallback');
})->with('handlers');

test('delete removes key', function ($handler) {
    $handler->set('key', 'value');
    $handler->delete('key');

    expect($handler->has('key'))->toBeFalse();
})->with('handlers');

test('clear removes all entries', function ($handler) {
    $handler->set('key1', 'value1');
    $handler->set('key2', 'value2');
    $handler->clear();

    expect($handler->has('key1'))->toBeFalse()
        ->and($handler->has('key2'))->toBeFalse();
})->with('handlers');

test('expired key is removed on has', function ($handler) {
    $handler->set('key', 'value', -1);

    expect($handler->has('key'))->toBeFalse();
})->with('handlers');

test('expired key returns default on get', function ($handler) {
    $handler->set('key', 'value', -1);

    expect($handler->get('key', 'fallback'))->toBe('fallback');
})->with('handlers');

test('overwrite updates value', function ($handler) {
    $handler->set('key', 'first');
    $handler->set('key', 'second');

    expect($handler->get('key'))->toBe('second');
})->with('handlers');

test('cache facade set and get round-trip', function ($cache) {
    $cache->set('key', 'value');

    expect($cache->get('key'))->toBe('value');
})->with('caches');

test('cache facade has returns true', function ($cache) {
    $cache->set('key', 'value');

    expect($cache->has('key'))->toBeTrue();
})->with('caches');

test('cache facade delete removes key', function ($cache) {
    $cache->set('key', 'value');
    $cache->delete('key');

    expect($cache->has('key'))->toBeFalse();
})->with('caches');

test('cache facade clear removes all', function ($cache) {
    $cache->set('key1', 'value1');
    $cache->set('key2', 'value2');
    $cache->clear();

    expect($cache->has('key1'))->toBeFalse()
        ->and($cache->has('key2'))->toBeFalse();
})->with('caches');

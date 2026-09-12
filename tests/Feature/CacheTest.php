<?php

use STDW\Cache\Cache;
use STDW\Cache\CacheConfig;

beforeEach(function () {
    $this->cache = new Cache(new CacheConfig([
        'handler' => 'file',
        'storage' => $this->testStorage,
    ]));
});

test('set and get returns value', function () {
    $this->cache->set('key', 'value');

    expect($this->cache->get('key'))->toBe('value');
});

test('set and has returns true', function () {
    $this->cache->set('key', 'value');

    expect($this->cache->has('key'))->toBeTrue();
});

test('get with nonexistent key returns default', function () {
    expect($this->cache->get('nonexistent', 'fallback'))->toBe('fallback');
});

test('get with nonexistent key returns null when no default', function () {
    expect($this->cache->get('nonexistent'))->toBeNull();
});

test('delete removes key', function () {
    $this->cache->set('key', 'value');
    $this->cache->delete('key');

    expect($this->cache->has('key'))->toBeFalse();
});

test('clear removes all entries', function () {
    $this->cache->set('key1', 'value1');
    $this->cache->set('key2', 'value2');
    $this->cache->clear();

    expect($this->cache->has('key1'))->toBeFalse()
        ->and($this->cache->has('key2'))->toBeFalse();
});

test('expired key is removed', function () {
    $this->cache->set('key', 'value', -1);

    expect($this->cache->has('key'))->toBeFalse();
});

test('overwrite updates value', function () {
    $this->cache->set('key', 'first');
    $this->cache->set('key', 'second');

    expect($this->cache->get('key'))->toBe('second');
});

test('multiple keys are independent', function () {
    $this->cache->set('key1', 'value1');
    $this->cache->set('key2', 'value2');

    expect($this->cache->get('key1'))->toBe('value1')
        ->and($this->cache->get('key2'))->toBe('value2');
});

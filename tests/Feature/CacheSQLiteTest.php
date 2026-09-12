<?php

use STDW\Cache\Cache;
use STDW\Cache\CacheConfig;

beforeEach(function () {
    $this->cache = new Cache(new CacheConfig([
        'handler' => 'sqlite',
        'storage' => $this->testStorage,
    ]));
});

test('set and get returns value', function () {
    $this->cache->set('key', 'value');

    expect($this->cache->get('key'))->toBe('value');
});

test('set and get with array', function () {
    $data = ['name' => 'John', 'age' => 30, 'tags' => ['admin', 'user']];
    $this->cache->set('user', $data);

    expect($this->cache->get('user'))->toBe($data);
});

test('set and get with object', function () {
    $obj = new \stdClass();
    $obj->name = 'John';
    $obj->age = 30;

    $this->cache->set('object', $obj);

    $result = $this->cache->get('object');
    expect($result->name)->toBe('John')
        ->and($result->age)->toBe(30);
});

test('set and get with empty string', function () {
    $this->cache->set('key', '');

    expect($this->cache->get('key'))->toBe('');
});

test('set and get with null', function () {
    $this->cache->set('key', null);

    expect($this->cache->get('key'))->toBeNull();
});

test('set and get with integer', function () {
    $this->cache->set('key', 42);

    expect($this->cache->get('key'))->toBe(42);
});

test('set and get with float', function () {
    $this->cache->set('key', 3.14);

    expect($this->cache->get('key'))->toBe(3.14);
});

test('set and get with boolean', function () {
    $this->cache->set('key', true);

    expect($this->cache->get('key'))->toBeTrue();
});

test('expired key returns default', function () {
    $this->cache->set('key', 'value', -1);

    expect($this->cache->get('key', 'fallback'))->toBe('fallback');
});

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

test('get returns null as default when not specified', function ($handler) {
    expect($handler->get('nonexistent'))->toBeNull();
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

test('multiple keys are independent', function ($handler) {
    $handler->set('key1', 'value1');
    $handler->set('key2', 'value2');

    expect($handler->get('key1'))->toBe('value1')
        ->and($handler->get('key2'))->toBe('value2');
})->with('handlers');

test('set returns true', function ($handler) {
    expect($handler->set('key', 'value'))->toBeTrue();
})->with('handlers');

test('clear returns true', function ($handler) {
    expect($handler->clear())->toBeTrue();
})->with('handlers');

test('set and get with array', function ($handler) {
    $data = ['name' => 'John', 'age' => 30, 'tags' => ['admin', 'user']];
    $handler->set('user', $data);

    $result = $handler->get('user');

    expect($result)->toBeInstanceOf(\stdClass::class)
        ->and($result->name)->toBe('John')
        ->and($result->age)->toBe(30)
        ->and($result->tags)->toBe(['admin', 'user']);
})->with('handlers');

test('set and get with object', function ($handler) {
    $obj = new \stdClass();
    $obj->name = 'John';
    $obj->age = 30;

    $handler->set('object', $obj);

    $result = $handler->get('object');
    expect($result->name)->toBe('John')
        ->and($result->age)->toBe(30);
})->with('handlers');

test('set and get with empty string', function ($handler) {
    $handler->set('key', '');

    expect($handler->get('key'))->toBe('');
})->with('handlers');

test('set and get with null', function ($handler) {
    $handler->set('key', null);

    expect($handler->get('key'))->toBeNull();
})->with('handlers');

test('set and get with integer', function ($handler) {
    $handler->set('key', 42);

    expect($handler->get('key'))->toBe(42);
})->with('handlers');

test('set and get with float', function ($handler) {
    $handler->set('key', 3.14);

    expect($handler->get('key'))->toBe(3.14);
})->with('handlers');

test('set and get with boolean', function ($handler) {
    $handler->set('key', true);

    expect($handler->get('key'))->toBeTrue();
})->with('handlers');

test('valid TTL preserves value immediately', function ($handler) {
    $handler->set('key', 'value', 300);

    expect($handler->has('key'))->toBeTrue()
        ->and($handler->get('key'))->toBe('value');
})->with('handlers');

test('cache facade set and get round-trip', function ($cache) {
    $cache->set('key', 'value');

    expect($cache->get('key'))->toBe('value');
})->with('caches');

test('cache facade has returns true', function ($cache) {
    $cache->set('key', 'value');

    expect($cache->has('key'))->toBeTrue();
})->with('caches');

test('cache facade has returns false for nonexistent key', function ($cache) {
    expect($cache->has('nonexistent'))->toBeFalse();
})->with('caches');

test('cache facade get with nonexistent key returns default', function ($cache) {
    expect($cache->get('nonexistent', 'fallback'))->toBe('fallback');
})->with('caches');

test('cache facade get with nonexistent key returns null when no default', function ($cache) {
    expect($cache->get('nonexistent'))->toBeNull();
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

test('cache facade expired key is removed', function ($cache) {
    $cache->set('key', 'value', -1);

    expect($cache->has('key'))->toBeFalse();
})->with('caches');

test('cache facade overwrite updates value', function ($cache) {
    $cache->set('key', 'first');
    $cache->set('key', 'second');

    expect($cache->get('key'))->toBe('second');
})->with('caches');

test('cache facade multiple keys are independent', function ($cache) {
    $cache->set('key1', 'value1');
    $cache->set('key2', 'value2');

    expect($cache->get('key1'))->toBe('value1')
        ->and($cache->get('key2'))->toBe('value2');
})->with('caches');

test('cache facade set and get with array', function ($cache) {
    $data = ['name' => 'John', 'age' => 30, 'tags' => ['admin', 'user']];
    $cache->set('user', $data);

    $result = $cache->get('user');

    expect($result)->toBeInstanceOf(\stdClass::class)
        ->and($result->name)->toBe('John')
        ->and($result->age)->toBe(30)
        ->and($result->tags)->toBe(['admin', 'user']);
})->with('caches');

test('cache facade set and get with object', function ($cache) {
    $obj = new \stdClass();
    $obj->name = 'John';
    $obj->age = 30;

    $cache->set('object', $obj);

    $result = $cache->get('object');
    expect($result->name)->toBe('John')
        ->and($result->age)->toBe(30);
})->with('caches');

test('cache facade set and get with empty string', function ($cache) {
    $cache->set('key', '');

    expect($cache->get('key'))->toBe('');
})->with('caches');

test('cache facade set and get with null', function ($cache) {
    $cache->set('key', null);

    expect($cache->get('key'))->toBeNull();
})->with('caches');

test('cache facade set and get with integer', function ($cache) {
    $cache->set('key', 42);

    expect($cache->get('key'))->toBe(42);
})->with('caches');

test('cache facade set and get with float', function ($cache) {
    $cache->set('key', 3.14);

    expect($cache->get('key'))->toBe(3.14);
})->with('caches');

test('cache facade set and get with boolean', function ($cache) {
    $cache->set('key', true);

    expect($cache->get('key'))->toBeTrue();
})->with('caches');

test('get with corrupted serialized data returns fallback for file handler', function () {
    $handler = new FileCacheHandler($this->testStorage);
    $handler->set('seed', 'ok');

    $hash = md5('corrupted');
    $shard = substr($hash, 0, 2);
    $dir = $this->testStorage . DIRECTORY_SEPARATOR . $shard;
    mkdir($dir, 0755, true);
    file_put_contents($dir . DIRECTORY_SEPARATOR . $hash . '.cache', (time() + 300) . "\nNOT_VALID_JSON");

    $result = $handler->get('corrupted', 'fallback');

    expect($result)->toBe('fallback');
});

test('get with corrupted serialized data returns fallback for sqlite handler', function () {
    $handler = new SqliteCacheHandler($this->testStorage);
    $handler->set('seed', 'ok');

    $reflection = new \ReflectionClass($handler);
    $property = $reflection->getProperty('storage');
    $property->setAccessible(true);
    $storage = $property->getValue($handler);

    $pdo = new \PDO('sqlite:' . $storage . '/cache.sqlite');
    $pdo->exec("INSERT OR REPLACE INTO cache (key, value, expires_at) VALUES ('corrupted', 'NOT_VALID_JSON', " . (time() + 300) . ')');

    $result = $handler->get('corrupted', 'fallback');

    expect($result)->toBe('fallback');
});

test('set and get with special character keys', function ($handler) {
    $specialKeys = [
        '../path/traversal',
        'key/with/slashes',
        'key with spaces',
        'chave com acentos: ção',
        'key-with-dashes_and_underscores',
        'key.with.dots',
        'key@with@symbols',
        str_repeat('a', 100),
    ];

    foreach ($specialKeys as $key) {
        $handler->set($key, "value_for_{$key}");

        expect($handler->get($key))->toBe("value_for_{$key}")
            ->and($handler->has($key))->toBeTrue();
    }
})->with('handlers');

test('set and get with long key', function ($handler) {
    $longKey = str_repeat('x', 255);

    $handler->set($longKey, 'long key value');

    expect($handler->get($longKey))->toBe('long key value')
        ->and($handler->has($longKey))->toBeTrue();
})->with('handlers');

test('set and get with large value', function ($handler) {
    $largeValue = str_repeat('data_payload_', 10000);

    $handler->set('large', $largeValue);

    expect($handler->get('large'))->toBe($largeValue);
})->with('handlers');

test('concurrent sequential writes to same key', function ($handler) {
    for ($i = 0; $i < 100; $i++) {
        $handler->set('counter', $i);
    }

    expect($handler->get('counter'))->toBe(99);
})->with('handlers');

test('set and get with nested structures', function ($handler) {
    $nested = [
        'level1' => [
            'level2' => [
                'level3' => ['deep' => true, 'value' => 42],
            ],
        ],
        'items' => [1, 2, 3],
    ];

    $handler->set('nested', $nested);

    $result = $handler->get('nested');

    expect($result->level1->level2->level3->deep)->toBeTrue()
        ->and($result->level1->level2->level3->value)->toBe(42)
        ->and($result->items)->toBe([1, 2, 3]);
})->with('handlers');

test('delete returns appropriate value for nonexistent key', function ($handler) {
    $result = $handler->delete('nonexistent');

    if ($handler instanceof FileCacheHandler) {
        expect($result)->toBeFalse();
    } else {
        expect($result)->toBeTrue();
    }
})->with('handlers');

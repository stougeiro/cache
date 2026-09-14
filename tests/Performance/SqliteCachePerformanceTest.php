<?php

use STDW\Cache\Handler\SqliteCacheHandler;

/*
|--------------------------------------------------------------------------
| SQLite Handler Performance Tests
|--------------------------------------------------------------------------
|
| Thresholds are conservative to avoid flaky tests on different hardware.
| SQLite with WAL mode should generally be faster than file-based caching.
| These tests measure throughput, not latency.
|
*/

beforeEach(function () {
    $this->handler = new SqliteCacheHandler($this->testStorage);
});

test('set 1000 keys performance', function () {
    $result = $this->measure(function ($i) {
        $this->handler->set("perf_set_{$i}", "value_{$i}");
    }, 1000);

    $this->assertPerformanceAbsolute($result, 3000, 'set 1000 keys');

    $this->handler->clear();
});

test('get 1000 keys performance (hot cache)', function () {
    for ($i = 0; $i < 1000; $i++) {
        $this->handler->set("perf_get_{$i}", "value_{$i}");
    }

    $result = $this->measure(function ($i) {
        $this->handler->get("perf_get_{$i}");
    }, 1000);

    $this->assertPerformanceAbsolute($result, 2000, 'get 1000 keys');

    $this->handler->clear();
});

test('has 1000 keys performance', function () {
    for ($i = 0; $i < 1000; $i++) {
        $this->handler->set("perf_has_{$i}", "value_{$i}");
    }

    $result = $this->measure(function ($i) {
        $this->handler->has("perf_has_{$i}");
    }, 1000);

    $this->assertPerformanceAbsolute($result, 1500, 'has 1000 keys');

    $this->handler->clear();
});

test('delete 1000 keys performance', function () {
    for ($i = 0; $i < 1000; $i++) {
        $this->handler->set("perf_del_{$i}", "value_{$i}");
    }

    $result = $this->measure(function ($i) {
        $this->handler->delete("perf_del_{$i}");
    }, 1000);

    $this->assertPerformanceAbsolute($result, 3000, 'delete 1000 keys');
});

test('mixed set and get 500 operations performance', function () {
    $result = $this->measure(function ($i) {
        $this->handler->set("perf_mix_{$i}", "value_{$i}");
        $this->handler->get("perf_mix_{$i}");
    }, 500);

    $this->assertPerformanceAbsolute($result, 3000, 'mixed set+get 500 ops');

    $this->handler->clear();
});

test('set 1000 unique keys performance', function () {
    $result = $this->measure(function ($i) {
        $this->handler->set("unique_key_{$i}_padding_to_make_it_longer", str_repeat('x', 100));
    }, 1000);

    $this->assertPerformanceAbsolute($result, 5000, 'set 1000 unique keys');

    $this->handler->clear();
});

test('get 1000 unique keys performance', function () {
    for ($i = 0; $i < 1000; $i++) {
        $this->handler->set("unique_get_{$i}_padding", str_repeat('x', 100));
    }

    $result = $this->measure(function ($i) {
        $this->handler->get("unique_get_{$i}_padding");
    }, 1000);

    $this->assertPerformanceAbsolute($result, 2000, 'get 1000 unique keys');

    $this->handler->clear();
});

test('clear 1000 entries performance', function () {
    for ($i = 0; $i < 1000; $i++) {
        $this->handler->set("clear_{$i}", "value_{$i}");
    }

    $result = $this->measure(function () {
        $this->handler->clear();
    }, 1);

    $this->assertPerformanceAbsolute($result, 1000, 'clear 1000 entries');
});

test('large value 1MB set and get performance', function () {
    $largeValue = str_repeat('data_payload_', 80000);

    $setResult = $this->measure(function () use ($largeValue) {
        $this->handler->set('large_perf', $largeValue);
    }, 10);

    $getResult = $this->measure(function () {
        $this->handler->get('large_perf');
    }, 10);

    $this->assertPerformanceAbsolute($setResult, 2000, 'large value set');
    $this->assertPerformanceAbsolute($getResult, 1500, 'large value get');

    $this->handler->clear();
});

test('expired key has() performance', function () {
    for ($i = 0; $i < 1000; $i++) {
        $this->handler->set("expired_{$i}", "value_{$i}", -1);
    }

    $result = $this->measure(function ($i) {
        $this->handler->has("expired_{$i}");
    }, 1000);

    $this->assertPerformanceAbsolute($result, 3000, 'expired key has() with lazy delete');
});

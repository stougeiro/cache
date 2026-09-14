<?php

use STDW\Cache\Handler\FileCacheHandler;
use STDW\Cache\Handler\SqliteCacheHandler;

/*
|--------------------------------------------------------------------------
| Handler Comparison Performance Tests
|--------------------------------------------------------------------------
|
| Runs the same benchmarks on both File and SQLite handlers and validates:
| 1. Absolute: each handler within its own threshold
| 2. Relative: neither handler is unreasonably slower than the other
|
*/

dataset('comparison_handlers', function () {
    $tmpFile = sys_get_temp_dir() . '/cache_perf_file_' . uniqid('', true);
    $tmpSqlite = sys_get_temp_dir() . '/cache_perf_sqlite_' . uniqid('', true);

    mkdir($tmpFile, 0755, true);
    mkdir($tmpSqlite, 0755, true);

    return [
        'file'   => [new FileCacheHandler($tmpFile)],
        'sqlite' => [new SqliteCacheHandler($tmpSqlite)],
    ];
});

test('set performance comparison', function ($handler) {
    $result = $this->measure(function ($i) use ($handler) {
        $handler->set("cmp_set_{$i}", "value_{$i}");
    }, 1000);

    $threshold = $handler instanceof FileCacheHandler ? 5000 : 3000;
    $this->assertPerformanceAbsolute($result, $threshold, 'set comparison');

    $handler->clear();
})->with('comparison_handlers');

test('get performance comparison', function ($handler) {
    for ($i = 0; $i < 1000; $i++) {
        $handler->set("cmp_get_{$i}", "value_{$i}");
    }

    $result = $this->measure(function ($i) use ($handler) {
        $handler->get("cmp_get_{$i}");
    }, 1000);

    $threshold = $handler instanceof FileCacheHandler ? 3000 : 2000;
    $this->assertPerformanceAbsolute($result, $threshold, 'get comparison');

    $handler->clear();
})->with('comparison_handlers');

test('has performance comparison', function ($handler) {
    for ($i = 0; $i < 1000; $i++) {
        $handler->set("cmp_has_{$i}", "value_{$i}");
    }

    $result = $this->measure(function ($i) use ($handler) {
        $handler->has("cmp_has_{$i}");
    }, 1000);

    $threshold = $handler instanceof FileCacheHandler ? 2000 : 1500;
    $this->assertPerformanceAbsolute($result, $threshold, 'has comparison');

    $handler->clear();
})->with('comparison_handlers');

test('delete performance comparison', function ($handler) {
    for ($i = 0; $i < 1000; $i++) {
        $handler->set("cmp_del_{$i}", "value_{$i}");
    }

    $result = $this->measure(function ($i) use ($handler) {
        $handler->delete("cmp_del_{$i}");
    }, 1000);

    $threshold = $handler instanceof FileCacheHandler ? 5000 : 3000;
    $this->assertPerformanceAbsolute($result, $threshold, 'delete comparison');
})->with('comparison_handlers');

test('clear performance comparison', function ($handler) {
    for ($i = 0; $i < 1000; $i++) {
        $handler->set("cmp_clear_{$i}", "value_{$i}");
    }

    $result = $this->measure(function () use ($handler) {
        $handler->clear();
    }, 1);

    $threshold = $handler instanceof FileCacheHandler ? 2000 : 1000;
    $this->assertPerformanceAbsolute($result, $threshold, 'clear comparison');
})->with('comparison_handlers');

test('large value performance comparison', function ($handler) {
    $largeValue = str_repeat('data_payload_', 80000);

    $setResult = $this->measure(function () use ($handler, $largeValue) {
        $handler->set('large_cmp', $largeValue);
    }, 10);

    $getResult = $this->measure(function () use ($handler) {
        $handler->get('large_cmp');
    }, 10);

    $setThreshold = $handler instanceof FileCacheHandler ? 3000 : 2000;
    $getThreshold = $handler instanceof FileCacheHandler ? 2000 : 1500;

    $this->assertPerformanceAbsolute($setResult, $setThreshold, 'large value set comparison');
    $this->assertPerformanceAbsolute($getResult, $getThreshold, 'large value get comparison');

    $handler->clear();
})->with('comparison_handlers');

test('expired key performance comparison', function ($handler) {
    for ($i = 0; $i < 1000; $i++) {
        $handler->set("cmp_expired_{$i}", "value_{$i}", -1);
    }

    $result = $this->measure(function ($i) use ($handler) {
        $handler->has("cmp_expired_{$i}");
    }, 1000);

    $threshold = $handler instanceof FileCacheHandler ? 5000 : 3000;
    $this->assertPerformanceAbsolute($result, $threshold, 'expired key comparison');

    $handler->clear();
})->with('comparison_handlers');

test('file vs sqlite relative performance: set', function () {
    $tmpFile = sys_get_temp_dir() . '/cache_rel_file_' . uniqid('', true);
    $tmpSqlite = sys_get_temp_dir() . '/cache_rel_sqlite_' . uniqid('', true);

    mkdir($tmpFile, 0755, true);
    mkdir($tmpSqlite, 0755, true);

    $fileHandler = new FileCacheHandler($tmpFile);
    $sqliteHandler = new SqliteCacheHandler($tmpSqlite);

    $fileResult = $this->measure(function ($i) use ($fileHandler) {
        $fileHandler->set("rel_set_{$i}", "value_{$i}");
    }, 1000);

    $sqliteResult = $this->measure(function ($i) use ($sqliteHandler) {
        $sqliteHandler->set("rel_set_{$i}", "value_{$i}");
    }, 1000);

    $ratio = $fileResult['total_ms'] / max($sqliteResult['total_ms'], 0.01);

    expect($ratio)->toBeLessThanOrEqual(10.0,
        "File set is {$ratio}x slower than SQLite (file: {$fileResult['total_ms']}ms, sqlite: {$sqliteResult['total_ms']}ms)");

    $fileHandler->clear();
    $sqliteHandler->clear();
});

test('file vs sqlite relative performance: get', function () {
    $tmpFile = sys_get_temp_dir() . '/cache_rel_file_' . uniqid('', true);
    $tmpSqlite = sys_get_temp_dir() . '/cache_rel_sqlite_' . uniqid('', true);

    mkdir($tmpFile, 0755, true);
    mkdir($tmpSqlite, 0755, true);

    $fileHandler = new FileCacheHandler($tmpFile);
    $sqliteHandler = new SqliteCacheHandler($tmpSqlite);

    for ($i = 0; $i < 1000; $i++) {
        $fileHandler->set("rel_get_{$i}", "value_{$i}");
        $sqliteHandler->set("rel_get_{$i}", "value_{$i}");
    }

    $fileResult = $this->measure(function ($i) use ($fileHandler) {
        $fileHandler->get("rel_get_{$i}");
    }, 1000);

    $sqliteResult = $this->measure(function ($i) use ($sqliteHandler) {
        $sqliteHandler->get("rel_get_{$i}");
    }, 1000);

    $ratio = $fileResult['total_ms'] / max($sqliteResult['total_ms'], 0.01);

    expect($ratio)->toBeLessThanOrEqual(10.0,
        "File get is {$ratio}x slower than SQLite (file: {$fileResult['total_ms']}ms, sqlite: {$sqliteResult['total_ms']}ms)");

    $fileHandler->clear();
    $sqliteHandler->clear();
});

test('summary output', function () {
    $tmpFile = sys_get_temp_dir() . '/cache_summary_file_' . uniqid('', true);
    $tmpSqlite = sys_get_temp_dir() . '/cache_summary_sqlite_' . uniqid('', true);

    mkdir($tmpFile, 0755, true);
    mkdir($tmpSqlite, 0755, true);

    $fileHandler = new FileCacheHandler($tmpFile);
    $sqliteHandler = new SqliteCacheHandler($tmpSqlite);

    $tests = [
        'set' => function ($handler, $i) { $handler->set("sum_{$i}", "value_{$i}"); },
        'get' => function ($handler, $i) { $handler->get("sum_{$i}"); },
        'has' => function ($handler, $i) { $handler->has("sum_{$i}"); },
        'delete' => function ($handler, $i) { $handler->delete("sum_{$i}"); },
    ];

    foreach ($tests as $op => $fn) {
        for ($i = 0; $i < 1000; $i++) {
            $fileHandler->set("sum_{$i}", "value_{$i}");
            $sqliteHandler->set("sum_{$i}", "value_{$i}");
        }

        $fileResult = $this->measure(fn($i) => $fn($fileHandler, $i), 1000);
        $sqliteResult = $this->measure(fn($i) => $fn($sqliteHandler, $i), 1000);

        $this->assertPerformanceAbsolute($fileResult, 5000, "file {$op}");
        $this->assertPerformanceAbsolute($sqliteResult, 3000, "sqlite {$op}");

        if ($op !== 'delete') {
            $fileHandler->clear();
            $sqliteHandler->clear();
        }
    }

    $fileHandler->clear();
    $sqliteHandler->clear();

    expect(true)->toBeTrue('Performance summary completed');
});

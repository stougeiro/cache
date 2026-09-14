<?php

use STDW\Cache\Handler\FileCacheHandler;

beforeEach(function () {
    $this->handler = new FileCacheHandler($this->testStorage);
});

test('set creates sharded subdirectory', function () {
    $this->handler->set('key', 'value');

    $hash = md5('key');
    $shard = substr($hash, 0, 2);
    $expectedDir = $this->testStorage . DIRECTORY_SEPARATOR . $shard;

    expect(is_dir($expectedDir))->toBeTrue()
        ->and(is_file($expectedDir . DIRECTORY_SEPARATOR . $hash . '.cache'))->toBeTrue();
});

test('file format has TTL on first line', function () {
    $this->handler->set('key', 'value', 300);

    $hash = md5('key');
    $path = $this->testStorage . DIRECTORY_SEPARATOR . substr($hash, 0, 2)
        . DIRECTORY_SEPARATOR . $hash . '.cache';

    $handle = fopen($path, 'r');
    $firstLine = fgets($handle);
    fclose($handle);

    $ttlTimestamp = (int) trim($firstLine);

    expect($ttlTimestamp)->toBeGreaterThan(time() + 290)
        ->and($ttlTimestamp)->toBeLessThanOrEqual(time() + 300);
});

test('has deletes expired file physically', function () {
    $this->handler->set('key', 'value', -1);

    $hash = md5('key');
    $path = $this->testStorage . DIRECTORY_SEPARATOR . substr($hash, 0, 2)
        . DIRECTORY_SEPARATOR . $hash . '.cache';

    expect(is_file($path))->toBeTrue();

    $this->handler->has('key');

    expect(is_file($path))->toBeFalse();
});

test('clear removes empty subdirectories', function () {
    $this->handler->set('key1', 'value1');
    $this->handler->set('key2', 'value2');

    $dirsBefore = glob($this->testStorage . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);

    expect(count($dirsBefore))->toBeGreaterThan(0);

    $this->handler->clear();

    $dirsAfter = glob($this->testStorage . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);

    expect($dirsAfter)->toBeEmpty();
});

test('delete returns false for nonexistent key', function () {
    expect($this->handler->delete('nonexistent'))->toBeFalse();
});

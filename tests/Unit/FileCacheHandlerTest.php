<?php

use STDW\Cache\Handler\FileCacheHandler;

beforeEach(function () {
    $this->handler = new FileCacheHandler($this->testStorage);
});

test('has returns false for nonexistent key', function () {
    expect($this->handler->has('nonexistent'))->toBeFalse();
});

test('get returns default for nonexistent key', function () {
    expect($this->handler->get('nonexistent', 'fallback'))->toBe('fallback');
});

test('get returns null as default when not specified', function () {
    expect($this->handler->get('nonexistent'))->toBeNull();
});

test('delete returns false for nonexistent key', function () {
    expect($this->handler->delete('nonexistent'))->toBeFalse();
});

test('set and has returns true', function () {
    $this->handler->set('key', 'value');

    expect($this->handler->has('key'))->toBeTrue();
});

test('set and get returns value', function () {
    $this->handler->set('key', 'value');

    expect($this->handler->get('key'))->toBe('value');
});

test('set overwrites previous value', function () {
    $this->handler->set('key', 'first');
    $this->handler->set('key', 'second');

    expect($this->handler->get('key'))->toBe('second');
});

test('delete removes key', function () {
    $this->handler->set('key', 'value');
    $this->handler->delete('key');

    expect($this->handler->has('key'))->toBeFalse();
});

test('clear removes all entries', function () {
    $this->handler->set('key1', 'value1');
    $this->handler->set('key2', 'value2');
    $this->handler->clear();

    expect($this->handler->has('key1'))->toBeFalse()
        ->and($this->handler->has('key2'))->toBeFalse();
});

test('expired key is removed on has', function () {
    $this->handler->set('key', 'value', -1);

    expect($this->handler->has('key'))->toBeFalse();
});

test('expired key returns default on get', function () {
    $this->handler->set('key', 'value', -1);

    expect($this->handler->get('key', 'fallback'))->toBe('fallback');
});

test('multiple keys are independent', function () {
    $this->handler->set('key1', 'value1');
    $this->handler->set('key2', 'value2');

    expect($this->handler->get('key1'))->toBe('value1')
        ->and($this->handler->get('key2'))->toBe('value2');
});

test('set returns true', function () {
    expect($this->handler->set('key', 'value'))->toBeTrue();
});

test('delete returns true for existing key', function () {
    $this->handler->set('key', 'value');

    expect($this->handler->delete('key'))->toBeTrue();
});

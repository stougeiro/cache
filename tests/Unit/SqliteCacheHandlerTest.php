<?php

use STDW\Cache\Handler\SqliteCacheHandler;

beforeEach(function () {
    $this->handler = new SqliteCacheHandler($this->testStorage);
});

test('has deletes expired row from database', function () {
    $this->handler->set('key', 'value', -1);

    $pdo = new PDO('sqlite:' . $this->testStorage . '/cache.sqlite');
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM cache WHERE key = :key');

    $stmt->execute(['key']);
    $countBefore = (int) $stmt->fetchColumn();

    expect($countBefore)->toBe(1);

    $this->handler->has('key');

    $stmt->execute(['key']);
    $countAfter = (int) $stmt->fetchColumn();

    expect($countAfter)->toBe(0);
});

test('set uses INSERT OR REPLACE for upsert', function () {
    $this->handler->set('key', 'first');
    $this->handler->set('key', 'second');

    $pdo = new PDO('sqlite:' . $this->testStorage . '/cache.sqlite');
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM cache WHERE key = :key');

    $stmt->execute(['key']);
    $count = (int) $stmt->fetchColumn();

    expect($count)->toBe(1)
        ->and($this->handler->get('key'))->toBe('second');
});

test('WAL journal mode is applied and persists', function () {
    $pdo = new PDO('sqlite:' . $this->testStorage . '/cache.sqlite');

    $journalMode = $pdo->query('PRAGMA journal_mode')->fetchColumn();

    expect($journalMode)->toBe('wal');
});

test('cache table has correct schema', function () {
    $pdo = new PDO('sqlite:' . $this->testStorage . '/cache.sqlite');

    $columns = [];
    $result = $pdo->query('PRAGMA table_info(cache)');

    while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
        $columns[] = $row['name'];
    }

    expect($columns)->toContain('key')
        ->and($columns)->toContain('value')
        ->and($columns)->toContain('expires_at');
});

test('expires_at index exists', function () {
    $pdo = new PDO('sqlite:' . $this->testStorage . '/cache.sqlite');

    $indexes = [];
    $result = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'index' AND tbl_name = 'cache'");

    while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
        $indexes[] = $row['name'];
    }

    expect($indexes)->toContain('idx_expires');
});

test('prepared statements are reused', function () {
    $this->handler->set('key1', 'value1');
    $this->handler->set('key2', 'value2');

    expect($this->handler->get('key1'))->toBe('value1')
        ->and($this->handler->get('key2'))->toBe('value2');

    $this->handler->delete('key1');

    expect($this->handler->get('key1'))->toBeNull()
        ->and($this->handler->get('key2'))->toBe('value2');
});

test('delete returns true even for nonexistent key', function () {
    expect($this->handler->delete('nonexistent'))->toBeTrue();
});

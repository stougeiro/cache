![PHP](https://img.shields.io/badge/PHP-%20^8.2-777BB4)
![PHPStan-Level](https://img.shields.io/badge/PHPStan-Level%209-224488)
![Pest-php](https://img.shields.io/badge/Tests-Passed-019733)
![License](https://img.shields.io/badge/License-MIT-777)

# Cache

A small, extensible cache library for PHP that abstracts storage behind a unified API. It ships with two handlers — FileCache and SQLite — both implementing TTL expiration, safe serialization, and predictable behavior. Swap handlers without touching your application code.

## ✨ Features

- **Unified API**  
  A single CacheInterface powering all handlers, keeping your application fully decoupled from the underlying storage engine.

- **Swappable Handlers**  
  Switch between FileCache and SQLite simply by changing the configuration — no application code needs to be modified.

- **TTL-Based Expiration**  
  Each entry automatically expires; stale items are removed on access to keep storage clean and predictable.

- **Safe Serialization**  
  Corrupted or unreadable values are safely discarded, preventing exceptions and inconsistent cache states.

- **File Sharding**  
  The file-based handler distributes cache entries into MD5‑based subdirectories, improving lookup performance and reducing filesystem contention.

- **Graceful Degradation**  
  Read/write failures or corrupted entries fall back to default values without breaking application flow.

- **Extensible Architecture**  
  Implement new handlers via `CacheHandlerInterface` and register them inside the `createHandler()` method of the `Cache` class.

---

## 📦 Installation

Install via Composer:

```bash
composer require stougeiro/cache
```

## 🚀 Usage Example

### Basic Usage

```php
use STDW\Cache\Cache;
use STDW\Cache\CacheConfig;

$config = [
    'handler' => 'file', // or 'sqlite'
    'storage' => __DIR__ . '/cache',
];

$cache = new Cache( new CacheConfig($config));

// Store a value for 5 minutes (300 seconds)
$cache->set('username', 'sidney', ttl: 300);

// Retrieve it
$user = $cache->get('username'); // "sidney"

// Check existence
if ($cache->has('username')) {
    echo "Cached!";
}

// Delete a single entry
$cache->delete('username');

// Clear all cache entries
$cache->clear();
```

---

## 🧠 Why?

Because cache libraries should be fast, simple, and extensible without being over-engineered. This package provides two production-ready handlers with different tradeoffs — file-based for simplicity, SQLite for concurrency — behind a single interface that can be swapped without changing application code.

The goal is to offer a cache layer that:
- avoids unnecessary abstractions,
- stays predictable and easy to debug,
- works in any environment (CLI, web, microservices),
- and can be extended with custom storage engines when needed.

---

## 🤝 Contributions

Contributions are welcome.
Feel free to open issues or submit pull requests.

<br><br>

[<img src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" width="170"/>](https://www.buymeacoffee.com/stougeiro)
<?php declare(strict_types=1);

    namespace STDW\Cache\Handler;

    use STDW\Cache\Spec\CacheHandlerInterface;

    use PDO;
    use PDOStatement;
    use Throwable;


    class SqliteCacheHandler implements CacheHandlerInterface
    {
        /** @var PDO
         */
        protected PDO $pdo;

        /** @var array<string, PDOStatement>
         */
        protected array $statement;


        public function __construct(
            protected string $storage)
        {
            $this->pdo = new PDO('sqlite:' . $storage . '/cache.sqlite');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

            $this->configure();
            $this->createTable();
            $this->prepareStatements();
        }


        /**
         * @param string $key
         * @return bool
         */
        public function has(string $key): bool
        {
            $this->statement['has']->execute([$key]);

            $expiresAt = $this->statement['has']->fetchColumn();

            if ($expiresAt === false) {
                return false;
            }

            /** @var string $expiresAt
             */

            if ((int) $expiresAt < time()) {
                $this->delete($key);

                return false;
            }

            return true;
        }

        /**
         * @param string $key
         * @param mixed $default
         * @return mixed
         */
        public function get(string $key, mixed $default = null): mixed
        {
            $this->statement['get']->execute([$key]);

            $row = $this->statement['get']->fetch(PDO::FETCH_ASSOC);

            if ( ! is_array($row)) {
                return $default;
            }

            /** @var array{value: string, expires_at: string} $row
             */

            if ((int) $row['expires_at'] < time()) {
                $this->delete($key);

                return $default;
            }

            try {
                return unserialize($row['value']);
            } catch (Throwable) {
                $this->delete($key);
            }

            return $default;
        }

        /**
         * @param string $key
         * @param mixed $value
         * @param int $ttl
         * @return bool
         */
        public function set(string $key, mixed $value, int $ttl = 300): bool
        {
            return $this->statement['set']->execute([
                $key, serialize($value), time() + $ttl,
            ]);
        }

        /**
         * @param string $key
         * @return bool
         */
        public function delete(string $key): bool
        {
            return $this->statement['delete']->execute([$key]);

        }

        /** @return bool
         */
        public function clear(): bool
        {
            return $this->statement['clear']->execute();
        }


        /**
         * @return void
         */
        protected function configure(): void
        {
            $this->pdo->exec('PRAGMA journal_mode = WAL');
            $this->pdo->exec('PRAGMA synchronous = OFF');
            $this->pdo->exec('PRAGMA cache_size = -2000');
            $this->pdo->exec('PRAGMA temp_store = MEMORY');
            $this->pdo->exec('PRAGMA mmap_size = 268435456');
            $this->pdo->exec('PRAGMA journal_size_limit = 67108864');
        }


        /**
         * @return void
         */
        protected function createTable(): void
        {
            $this->pdo->exec('
                CREATE TABLE IF NOT EXISTS cache (
                    key TEXT PRIMARY KEY,
                    value BLOB NOT NULL,
                    expires_at INTEGER NOT NULL
                )
            ');

            $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_expires ON cache(expires_at)');
        }


        /**
         * @return void
         */
        protected function prepareStatements(): void
        {
            $this->statement = [
                'has' => $this->pdo->prepare(
                    'SELECT expires_at FROM cache WHERE key = :key'
                ),

                'get' => $this->pdo->prepare(
                    'SELECT value, expires_at FROM cache WHERE key = :key'
                ),

                'set' => $this->pdo->prepare(
                    'INSERT OR REPLACE INTO cache (key, value, expires_at) VALUES (:key, :value, :expires_at)'
                ),

                'delete' => $this->pdo->prepare(
                    'DELETE FROM cache WHERE key = :key'
                ),

                'clear' => $this->pdo->prepare(
                    'DELETE FROM cache'
                ),
            ];
        }
    }

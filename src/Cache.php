<?php declare(strict_types=1);

    namespace STDW\Cache;

    use STDW\Contract\Cache\CacheInterface;
    use STDW\Cache\Spec\CacheConfigInterface;
    use STDW\Cache\Spec\CacheHandlerInterface;
    use STDW\Cache\Handler\FileCacheHandler;
    use STDW\Cache\Handler\SqliteCacheHandler;


    class Cache implements CacheInterface
    {
        protected CacheHandlerInterface $handler;


        public function __construct(
            protected CacheConfigInterface $config)
        {
            $this->handler = $this->createHandler($config);
        }


        /**
         * @param string $key
         * @return bool
         */
        public function has(string $key): bool
        {
            return $this->handler->has($key);
        }

        /**
         * @param string $key
         * @param mixed $default
         * @return mixed
         */
        public function get(string $key, mixed $default = null): mixed
        {
            return $this->handler->get($key, $default);
        }

        /**
         * @param string $key
         * @param mixed $value
         * @param int $ttl
         * @return bool
         */
        public function set(string $key, mixed $value, int $ttl = 300): bool
        {
            return $this->handler->set($key, $value, $ttl);
        }

        /**
         * @param string $key
         * @return bool
         */
        public function delete(string $key): bool
        {
            return $this->handler->delete($key);
        }

        /** @return bool
         */
        public function clear(): bool
        {
            return $this->handler->clear();
        }


        /**
         * @param CacheConfigInterface $config 
         * @return CacheHandlerInterface 
         */
        protected function createHandler(CacheConfigInterface $config): CacheHandlerInterface
        {
            return match ($config->handler()) {
                'sqlite' => new SqliteCacheHandler($config->storage()),
                default  => new FileCacheHandler($config->storage()),
            };
        }
    }

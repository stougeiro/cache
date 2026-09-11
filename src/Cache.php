<?php declare(strict_types=1);

    namespace STDW\Cache;

    use STDW\Contract\Cache\CacheInterface;

    class Cache implements CacheInterface
    {
        /**
         * @param string $key 
         * @return bool 
         */
        public function has(string $key): bool
        {
            return false;
        }

        /**
         * @param string $key 
         * @param mixed $default 
         * @return mixed 
         */
        public function get(string $key, mixed $default = null): mixed
        {
            return '';
        }

        /**
         * @param string $key 
         * @param mixed $value 
         * @param int $ttl 
         * @return bool 
         */
        public function set(string $key, mixed $value, int $ttl = 300): bool
        {
            return false;
        }

        /**
         * @param string $key 
         * @return bool 
         */
        public function delete(string $key): bool
        {
            return false;
        }

        /** @return bool 
         */
        public function clear(): bool
        {
            return false;
        }
    }

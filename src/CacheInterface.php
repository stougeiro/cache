<?php declare(strict_types=1);

    namespace STDW\Contract\Cache;


    interface CacheInterface
    {
        /**
         * @param string $key 
         * @return bool 
         */
        public function has(string $key): bool;

        /**
         * @param string $key 
         * @param mixed $default 
         * @return mixed 
         */
        public function get(string $key, mixed $default = null): mixed;

        /**
         * @param string $key 
         * @param mixed $value 
         * @param int $ttl 
         * @return bool 
         */
        public function set(string $key, mixed $value, int $ttl = 300): bool;

        /**
         * @param string $key 
         * @return bool 
         */
        public function delete(string $key): bool;

        /** @return bool 
         */
        public function clear(): bool;
    }

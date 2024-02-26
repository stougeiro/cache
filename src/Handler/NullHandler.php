<?php declare(strict_types=1);

    namespace STDW\Cache\Handler;

    use STDW\Cache\Contract\CacheHandlerInterface;


    class NullHandler implements CacheHandlerInterface
    {
        public function __construct()
        { }


        public function has(string $key): bool
        {
            return false;
        }

        public function get(string $key, mixed $default = null): mixed
        {
            return null;
        }

        public function getMultiple(iterable $keys, mixed $default = null): iterable
        {
            return [];
        }

        public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
        {
            return true;
        }

        public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
        {
            return true;
        }

        public function delete(string $key): bool
        {
            return true;
        }

        public function deleteMultiple(iterable $keys): bool
        {
            return true;
        }

        public function clear(): bool
        {
            return true;
        }
    }
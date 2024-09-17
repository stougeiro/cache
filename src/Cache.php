<?php declare(strict_types=1);

    namespace STDW\Cache;

    use STDW\Cache\Contract\CacheHandlerInterface;
    use STDW\Cache\Contract\CacheInterface;


    class Cache implements CacheInterface
    {
        public function __construct(
            protected CacheHandlerInterface $handler)
        { }


        public function get(string $key, mixed $default = null): mixed
        {
            return $this->handler->get($key, $default);
        }

        public function getMultiple(iterable $keys, mixed $default = null): iterable
        {
            return $this->handler->getMultiple($keys, $default);
        }

        public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
        {
            return $this->handler->set($key, $value, $ttl);
        }

        public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
        {
            return $this->handler->setMultiple($values, $ttl);
        }

        public function delete(string $key): bool
        {
            return $this->handler->delete($key);
        }

        public function deleteMultiple(iterable $keys): bool
        {
            return $this->handler->deleteMultiple($keys);
        }

        public function clear(): bool
        {
            return $this->handler->clear();
        }

        public function has(string $key): bool
        {
            return $this->handler->has($key);
        }
    }
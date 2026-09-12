<?php declare(strict_types=1);

    namespace STDW\Cache;

    use STDW\Cache\Spec\CacheManagerAbstracted;
    use STDW\Cache\Spec\CacheConfigInterface;
    use STDW\Cache\Spec\CacheHandlerInterface;
    use STDW\Cache\Handler\SqliteCacheHandler;
    use STDW\Cache\Handler\FileCacheHandler;


    class Cache extends CacheManagerAbstracted
    {
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

<?php declare(strict_types=1);

    use STDW\Cache\Contract\CacheInterface;


    if ( ! function_exists('cache'))
    {
        function cache(): CacheInterface
        {
            return app()->make(CacheInterface::class);
        }
    }
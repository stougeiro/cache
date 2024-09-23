<?php declare(strict_types=1);

    use STDW\Cache\Cache;


    if ( ! function_exists('cache'))
    {
        function cache(): Cache
        {
            return app()->make(Cache::class);
        }
    }
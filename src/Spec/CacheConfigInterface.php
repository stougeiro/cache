<?php declare(strict_types=1);

    namespace STDW\Cache\Spec;


    interface CacheConfigInterface
    {
        /** @return string
         */
        public function handler(): string;

        /** @return string
         */
        public function storage(): string;
    }

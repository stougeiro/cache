<?php declare(strict_types=1);

    namespace STDW\Cache;


    class CacheConfig
    {
        /** @var string
         */
        protected string $handler;

        /** @var string
         */
        protected string $storage;


        /**
         * @param array{
         *    handler?: string,
         *    storage?: string,
         * } $config 
         */
        public function __construct(array $config)
        {
            $defaults = [
                'handler' => 'file',
                'storage' => '',
            ];

            $config = array_replace($defaults, $config);

            $this->handler = $this->validateHandler($config['handler']);
            $this->storage = $this->validateStorage($config['storage']);
        }


        /** @return string 
         */
        public function handler(): string
        {
            return $this->handler;
        }

        /** @return string 
         */
        public function storage(): string
        {
            return $this->storage;
        }


        /**
         * @param string $handler 
         * @return string 
         */
        protected function validateHandler(string $handler): string
        {
            return match ($handler) {
                'file', 'sqlite' => $handler,

                default => 'file',
            };
        }

        /**
         * @param string $path 
         * @return string 
         */
        protected function validateStorage(string $path): string
        {
            if (is_dir($path) && is_writable($path)) {
                return $path;
            }

            return $this->getDefaultSavePath();
        }

        /** @return string 
         */
        protected function getDefaultSavePath(): string
        {
            return sys_get_temp_dir();
        }
    }

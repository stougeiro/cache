<?php declare(strict_types=1);

    namespace STDW\Cache\Handler;

    use STDW\Cache\Spec\CacheHandlerInterface;

    use Throwable;
    use RecursiveIteratorIterator;
    use RecursiveDirectoryIterator;
    use FilesystemIterator;


    class FileCacheHandler implements CacheHandlerInterface
    {
        public function __construct(
            protected string $storage)
        { }


        /**
         * @param string $key
         * @return bool
         */
        public function has(string $key): bool
        {
            $path = $this->path($key);
            $handle = fopen($path, 'r');

            if ($handle === false) {
                return false;
            }

            $line = fgets($handle);
            fclose($handle);

            if ($line === false || ((int) $line) < time()) {
                unlink($path);

                return false;
            }

            return true;
        }

        /**
         * @param string $key
         * @param mixed $default
         * @return mixed
         */
        public function get(string $key, mixed $default = null): mixed
        {
            $path = $this->path($key);
            $handle = fopen($path, 'r');

            if ($handle === false) {
                return $default;
            }

            $line = fgets($handle);

            if ($line === false || ((int) $line) < time()) {
                fclose($handle);
                unlink($path);

                return $default;
            }

            $data = stream_get_contents($handle);
            fclose($handle);

            try {
                return unserialize($data);
            } catch (Throwable) {
                unlink($path);
            }

            return $default;
        }

        /**
         * @param string $key
         * @param mixed $value
         * @param int $ttl
         * @return bool
         */
        public function set(string $key, mixed $value, int $ttl = 300): bool
        {
            $path = $this->path($key, true);
            $content = (time() + $ttl) ."\n". serialize($value);

            return file_put_contents($path, $content, LOCK_EX) !== false;
        }

        /**
         * @param string $key
         * @return bool
         */
        public function delete(string $key): bool
        {
            $path = $this->path($key);

            if (is_file($path)) {
                return unlink($path);
            }

            return false;
        }

        /** @return bool
         */
        public function clear(): bool
        {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->storage, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                    continue;
                }

                if ($file->isFile() && $file->getExtension() === 'cache') {
                    unlink($file->getRealPath());
                }
            }

            return true;
        }


        /**
         * @param string $key
         * @return string
         */
        protected function path(string $key, bool $ensureDirectory = false): string
        {
            $hash = md5($key);
            $dir = $this->storage . DIRECTORY_SEPARATOR . substr($hash, 0, 2) . DIRECTORY_SEPARATOR;

            if ($ensureDirectory && ! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            return $dir . $hash.'.cache';
        }
    }

<?php

namespace Cmp\Storage\Strategy;

use Cmp\Storage\AdapterInterface;
use Cmp\Storage\Exception\FileExistsException;
use Cmp\Storage\Exception\FileNotFoundException;
use Psr\Log\LogLevel;

/**
 * Class FallBackChainStrategy.
 */
class FallBackChainStrategy extends AbstractStorageCallStrategy
{
    public function getStrategyName()
    {
        return 'FallBackChainStrategy';
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function exists($path)
    {
        $fn = function (AdapterInterface $adapter) use ($path) {
            return $adapter->exists($path);
        };

        return $this->runChainAndLog($fn);
    }

    /**
     * Read a file.
     *
     * @param string $path
     *
     * @return mixed
     *
     * @throws FileNotFoundException
     */
    public function get($path)
    {
        $fn = function (AdapterInterface $adapter) use ($path) {
            return $adapter->get($path);
        };

        return $this->runChainAndLog($fn);
    }

    /**
     * Retrieves a read-stream for a path.
     *
     * @param string $path The path to the file
     *
     * @throws \Cmp\Storage\Exception\FileNotFoundException
     *
     * @return resource The path resource or false on failure
     */
    public function getStream($path)
    {
        $fn = function (AdapterInterface $adapter) use ($path) {
            return $adapter->getStream($path);
        };

        return $this->runChainAndLog($fn);
    }

    /**
     * Rename a file.
     *
     * @param string $path      Path to the existing file
     * @param string $newpath   The new path of the file
     * @param bool   $overwrite
     *
     * @return bool
     *
     * @throws FileExistsException Thrown if $newpath exists
     */
    public function rename($path, $newpath, $overwrite = false)
    {
        $fn = function (AdapterInterface $adapter) use ($path, $newpath, $overwrite) {
            return $adapter->rename($path, $newpath, $overwrite);
        };

        return $this->runChainAndLog($fn);
    }

    /**
     * Copy a file.
     *
     * @param string $path      Path to the existing file
     * @param string $newpath   The new path of the file
     *
     * @return bool
     *
     * @throws FileExistsException Thrown if $newpath exists
     */
    public function copy($path, $newpath)
    {
        $fn = function (AdapterInterface $adapter) use ($path, $newpath) {
            return $adapter->copy($path, $newpath);
        };

        return $this->runChainAndLog($fn);
    }

    /**
     * Delete a file.
     *
     * @param string $path
     *
     * @throws \Cmp\Storage\Exception\FileNotFoundException
     *
     * @return bool True on success, false on failure
     */
    public function delete($path)
    {
        $fn = function (AdapterInterface $adapter) use ($path) {
            return $adapter->delete($path);
        };

        return $this->runChainAndLog($fn);
    }

    /**
     * Create a file or update if exists. It will create the missing folders.
     *
     * @param string $path     The path to the file
     * @param string $contents The file contents
     *
     * @return bool True on success, false on failure
     *
     * @throws \Cmp\Storage\Exception\InvalidPathException
     */
    public function put($path, $contents)
    {
        $fn = function (AdapterInterface $adapter) use ($path, $contents) {
            return $adapter->put($path, $contents);
        };

        return $this->runChainAndLog($fn);
    }

    /**
     * Create a file or update if exists. It will create the missing folders.
     *
     * @param string   $path     The path to the file
     * @param resource $resource The file handle
     *
     * @throws \Cmp\Storage\InvalidArgumentException Thrown if $resource is not a resource
     *
     * @return bool True on success, false on failure
     */
    public function putStream($path, $resource)
    {
        $fn = function (AdapterInterface $adapter) use ($path, $resource) {
            return $adapter->putStream($path, $resource);
        };

        return $this->runChainAndLog($fn);
    }

    /**
     * Executes the operation in all adapters, returning on the first success or false if at least one executed the
     * operation without raising exceptions.
     *
     * @param callable $fn
     *
     * @return mixed If all adapters raised exceptions, the first one will be thrown
     *
     * @throws bool
     */
    private function runChainAndLog(callable $fn)
    {
        $firstException = false;
        $call = false;
        $result = false;
        foreach ($this->getAdapters() as $adapter) {
            try {
                $result = $fn($adapter);
                $call = true;
                if ($result !== false) {
                    return $result;
                }
            } catch (\Exception $exception) {
                if (!$firstException) {
                    $firstException = $exception;
                }
                $this->logAdapterException($adapter, $exception);
            }
        }

        // Result will be set if at least one adapters executed the operation without exceptions
        if ($call) {
            return $result;
        }

        throw $firstException;
    }

    /**
     * @param \Cmp\Storage\VirtualStorageInterface $adapter
     * @param \Exception                           $e
     */
    private function logAdapterException($adapter, $e)
    {
        $this->log(
            LogLevel::ERROR,
            'Adapter "'.$adapter->getName().'" fails.',
            ['exception' => $e]
        );
    }
}

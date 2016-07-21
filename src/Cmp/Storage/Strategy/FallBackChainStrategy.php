<?php

namespace Cmp\Storage\Strategy;

use Cmp\Storage\Exception\FileNotFoundException;
use Cmp\Storage\Exception\InvalidPathException;
use InvalidArgumentException;

/**
 * Class FallBackChainStrategy
 *
 * @package Cmp\Storage\Strategy
 */
class FallBackChainStrategy extends AbstractStorageCallStrategy
{
    public function getStrategyName()
    {
        return "FallBackChainStrategy";
    }

    /**
     * @param string $path
     *
     * @return mixed
     * @throws FileNotFoundException
     */
    public function exists($path)
    {
        $fn = function ($adapter) use ($path) {
            return $adapter->exists($path);
        };

        return $this->runChainAndLog($fn, new FileNotFoundException());
    }

    /**
     * Read a file.
     *
     * @param string $path
     *
     * @return mixed
     * @throws FileNotFoundException
     */
    public function get($path)
    {
        $fn = function ($adapter) use ($path) {
            return $adapter->get($path);
        };

        return $this->runChainAndLog($fn, new FileNotFoundException());
    }

    /**
     * Retrieves a read-stream for a path.
     *
     * @param string $path The path to the file.
     *
     * @throws \Cmp\Storage\Exception\FileNotFoundException
     *
     * @return resource The path resource or false on failure.
     */
    public function getStream($path)
    {
        $fn = function ($adapter) use ($path) {
            return $adapter->getStream($path);
        };

        return $this->runChainAndLog($fn, new FileNotFoundException());
    }

    /**
     * Rename a file.
     *
     * @param string $path    Path to the existing file.
     * @param string $newpath The new path of the file.
     *
     * @throws \Cmp\Storage\Exception\\FileExistsException   Thrown if $newpath exists.
     * @throws \Cmp\Storage\Exception\FileNotFoundException Thrown if $path does not exist.
     *
     * @return bool True on success, false on failure.
     */
    public function rename($path, $newpath)
    {
        $fn = function ($adapter) use ($path, $newpath) {
            return $adapter->rename($path, $newpath);
        };

        return $this->runChainAndLog($fn, new FileNotFoundException());
    }

    /**
     * Delete a file.
     *
     * @param string $path
     *
     * @throws \Cmp\Storage\Exception\FileNotFoundException
     *
     * @return bool True on success, false on failure.
     */
    public function delete($path)
    {
        $fn = function ($adapter) use ($path) {
            return $adapter->delete($path);
        };

        return $this->runChainAndLog($fn, new FileNotFoundException());
    }

    /**
     * Create a file or update if exists. It will create the missing folders.
     *
     * @param string $path     The path to the file.
     * @param string $contents The file contents.
     *
     * @return bool True on success, false on failure.
     * @throws \Cmp\Storage\Exception\InvalidPathException
     */
    public function put($path, $contents)
    {
        $fn = function ($adapter) use ($path, $contents) {
            return $adapter->put($path, $contents);
        };

        return $this->runChainAndLog($fn, new InvalidPathException());
    }

    /**
     * Create a file or update if exists. It will create the missing folders.
     *
     * @param string   $path     The path to the file.
     * @param resource $resource The file handle.
     *
     * @throws \Cmp\Storage\InvalidArgumentException Thrown if $resource is not a resource.
     *
     * @return bool True on success, false on failure.
     */
    public function putStream($path, $resource)
    {
        $fn = function ($adapter) use ($path, $resource) {
            return $adapter->putStream($path, $resource);
        };

        return $this->runChainAndLog($fn, new InvalidArgumentException());
    }

    /**
     * @param callable   $fn
     * @param \Exception $exception
     *
     * @return mixed
     * @throws \Exception
     */
    private function runChainAndLog(callable $fn, \Exception $exception)
    {
        foreach ($this->getAdapters() as $adapter) {
            try {
                return $fn($adapter);
            } catch (\Exception $e) {
                $this->logAdapterException($adapter, $e);
            }
        }
        throw $exception;
    }

    /**
     * @param $adapter
     * @param $e
     */
    private function logAdapterException($adapter, $e)
    {
        $this->log(
            LOG_ERR,
            'Adapter "'.$adapter->getName().'" fails.',
            ['exception' => $e]
        );
    }
}

<?php

namespace Cmp\Storage\Strategy;

use Cmp\Storage\Exception\FileNotFoundException;
use Cmp\Storage\Exception\InvalidPathException;
use InvalidArgumentException;

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
        foreach ($this->getAdapters() as $adapter) {
            try {
                return $adapter->exists($path);
            } catch (\Exception $e) {
                $this->log(LOG_ERR, "Adapter ".$adapter->getName()." fails on".__FUNCTION__, ['exception' => $e]);
            }
        }
        throw new FileNotFoundException();
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
        foreach ($this->getAdapters() as $adapter) {
            try {
                return $adapter->get($path);
            } catch (\Exception $e) {
                $this->log(LOG_ERR, "Adapter ".$adapter->getName()." fails on".__FUNCTION__, ['exception' => $e]);
            }
        }
        throw new FileNotFoundException();
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
        foreach ($this->getAdapters() as $adapter) {
            try {
                return $adapter->getStream($path);
            } catch (\Exception $e) {
                $this->log(LOG_ERR, "Adapter ".$adapter->getName()." fails on".__FUNCTION__, ['exception' => $e]);
            }
        }
        throw new FileNotFoundException();
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
        foreach ($this->getAdapters() as $adapter) {
            try {
                return $adapter->rename($path);
            } catch (\Exception $e) {
                $this->log(LOG_ERR, "Adapter ".$adapter->getName()." fails on".__FUNCTION__, ['exception' => $e]);
            }
        }
        throw new FileNotFoundException();
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
        foreach ($this->getAdapters() as $adapter) {
            try {
                return $adapter->delete($path);
            } catch (\Exception $e) {
                $this->log(LOG_ERR, "Adapter ".$adapter->getName()." fails on".__FUNCTION__, ['exception' => $e]);
            }
        }
        throw new FileNotFoundException();
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
        foreach ($this->getAdapters() as $adapter) {
            try {
                return $adapter->put($path);
            } catch (\Exception $e) {
                $this->log(LOG_ERR, "Adapter ".$adapter->getName()." fails on".__FUNCTION__, ['exception' => $e]);
            }
        }
        throw new InvalidPathException();
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
        foreach ($this->getAdapters() as $adapter) {
            try {
                return $adapter->putStream($path);
            } catch (\Exception $e) {
                $this->log(LOG_ERR, "Adapter ".$adapter->getName()." fails on".__FUNCTION__, ['exception' => $e]);
            }
        }
        throw new InvalidArgumentException();
    }
}

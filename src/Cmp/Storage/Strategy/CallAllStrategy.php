<?php

namespace Cmp\Storage\Strategy;

use Cmp\Storage\AdapterInterface;

/**
 * Class CallAllStrategy.
 */
class CallAllStrategy extends AbstractStorageCallStrategy
{
    use  RunAndLogTrait;

    public function getStrategyName()
    {
        return 'CallAllStrategy';
    }

    /**
     * Check whether a file exists.
     *
     * @param string $path
     *
     * @return bool
     */
    public function exists($path)
    {
        $fn = function (AdapterInterface $adapter) use ($path) {
            return $adapter->exists($path);
        };

        return $this->runAll($fn);
    }

    /**
     * Read a file.
     *
     * @param string $path The path to the file
     *
     * @throws \Cmp\Storage\Exception\FileNotFoundException
     *
     * @return string The file contents or false on failure
     */
    public function get($path)
    {
        $fn = function (AdapterInterface $adapter) use ($path) {
            return $adapter->get($path);
        };

        return $this->logOnFalse($this->runOne($fn, $path), "Impossible get file: {file}.", ['file' => $path]);
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

        return $this->logOnFalse(
            $this->runOne($fn, $path),
            "Impossible get stream form file: {file}.",
            ['file' => $path]
        );
    }

    /**
     * Rename a file.
     *
     * @param string $path      Path to the existing file
     * @param string $newpath   The new path of the file
     * @param bool   $overwrite Thrown an Exception if $newpath exists
     *
     * @return bool
     */
    public function rename($path, $newpath, $overwrite = false)
    {
        $fn = function (AdapterInterface $adapter) use ($path, $newpath, $overwrite) {
            return $adapter->rename($path, $newpath, $overwrite);
        };

        return $this->logOnFalse(
            $this->runAll($fn),
            "Impossible rename file from {from} to {to}.",
            ['from' => $path, 'to' => $newpath]
        );
    }

    /**
     * Copy a file.
     *
     * @param string $path    Path to the existing file
     * @param string $newpath The new path of the file
     *
     * @return bool
     */
    public function copy($path, $newpath)
    {
        $fn = function (AdapterInterface $adapter) use ($path, $newpath) {
            return $adapter->copy($path, $newpath);
        };

        return $this->logOnFalse(
            $this->runAll($fn),
            "Impossible copy file from {from} to {to}.",
            ['from' => $path, 'to' => $newpath]
        );
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

        return $this->logOnFalse($this->runAll($fn), "Impossible delete file {file}.", ['file' => $path]);
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

        return $this->logOnFalse($this->runAll($fn), "Impossible put file {file}.", ['file' => $path]);
    }

    /**
     * Create a file or update if exists. It will create the missing folders.
     *
     * @param string   $path     The path to the file
     * @param resource $resource The file handle
     *
     * @throws \InvalidArgumentException Thrown if $resource is not a resource
     *
     * @return bool True on success, false on failure
     */
    public function putStream($path, $resource)
    {
        $fn = function (AdapterInterface $adapter) use ($path, $resource) {
            return $adapter->putStream($path, $resource);
        };

        return $this->logOnFalse($this->runAll($fn), "Impossible put file stream {file}.", ['file' => $path]);
    }
}

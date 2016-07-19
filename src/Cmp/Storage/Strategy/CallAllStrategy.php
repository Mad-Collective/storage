<?php

namespace Cmp\Storage\Strategy;

class CallAllStrategy extends AbstractStorageCallStrategy
{


    public function getStrategyName()
    {
        return "CallAllStrategy";
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
        $result = false;

        foreach ($this->getAdapters() as $adapter) {
            try {
                $result = $adapter->exists($path) || $result;
            } catch (\Exception $e) {
                $this->log(
                    LOG_ERR,
                    'Adapter "'.$adapter->getName().'" fails on '.__FUNCTION__.' call.',
                    ['exception' => $e]
                );
            }
        }

        return $result;
    }

    /**
     * Read a file.
     *
     * @param string $path The path to the file.
     *
     * @throws \Cmp\Storage\Exception\FileNotFoundException
     *
     * @return string The file contents or false on failure.
     */
    public function get($path)
    {
        $result = false;

        foreach ($this->getAdapters() as $adapter) {
            try {
                if ($result = $adapter->get($path)) {
                    return $result;
                }
            } catch (\Exception $e) {
                $this->log(
                    LOG_ERR,
                    'Adapter "'.$adapter->getName().'" fails on '.__FUNCTION__.' call.',
                    ['exception' => $e]
                );
            }
        }

        return $result;
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
        $result = false;

        foreach ($this->getAdapters() as $adapter) {
            try {
                if ($result = $adapter->getStream($path)) {
                    return $result;
                }
            } catch (\Exception $e) {
                $this->log(
                    LOG_ERR,
                    'Adapter "'.$adapter->getName().'" fails on '.__FUNCTION__.' call.',
                    ['exception' => $e]
                );
            }
        }

        return $result;
    }

    /**
     * Rename a file.
     *
     * @param string $path    Path to the existing file.
     * @param string $newpath The new path of the file.
     *
     * @throws \Cmp\Storage\Exception\FileExistsException   Thrown if $newpath exists.
     * @throws \Cmp\Storage\Exception\FileNotFoundException Thrown if $path does not exist.
     *
     * @return bool True on success, false on failure.
     */
    public function rename($path, $newpath)
    {
        $result = false;

        foreach ($this->getAdapters() as $adapter) {
            try {
                $result = $adapter->rename($path, $newpath) || $result;
            } catch (\Exception $e) {
                $this->log(
                    LOG_ERR,
                    'Adapter "'.$adapter->getName().'" fails on '.__FUNCTION__.' call.',
                    ['exception' => $e]
                );
            }
        }

        return $result;
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
        $result = false;
        foreach ($this->getAdapters() as $adapter) {
            try {
                $result = $adapter->delete($path) || $result;
            } catch (\Exception $e) {
                $this->log(
                    LOG_ERR,
                    'Adapter "'.$adapter->getName().'" fails on '.__FUNCTION__.' call.',
                    ['exception' => $e]
                );
            }
        }

        return $result;
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
        $result = false;

        foreach ($this->getAdapters() as $adapter) {
            try {
                $result = $adapter->put($path, $contents) || $result;
            } catch (\Exception $e) {
                $this->log(
                    LOG_ERR,
                    'Adapter "'.$adapter->getName().'" fails on '.__FUNCTION__.' call.',
                    ['exception' => $e]
                );
            }
        }

        return $result;
    }

    /**
     * Create a file or update if exists. It will create the missing folders.
     *
     * @param string   $path     The path to the file.
     * @param resource $resource The file handle.
     *
     * @throws \Cmp\Storage\Exception\InvalidArgumentException Thrown if $resource is not a resource.
     *
     * @return bool True on success, false on failure.
     */
    public function putStream($path, $resource)
    {
        $result = false;

        foreach ($this->getAdapters() as $adapter) {
            try {
                $result = $adapter->putStream($path, $resource) || $result;
            } catch (\Exception $e) {
                $this->log(
                    LOG_ERR,
                    'Adapter "'.$adapter->getName().'" fails on '.__FUNCTION__.' call.',
                    ['exception' => $e]
                );
            }
        }

        return $result;
    }
}

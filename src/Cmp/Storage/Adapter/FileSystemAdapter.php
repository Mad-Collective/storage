<?php

namespace Cmp\Storage\Adapter;

use Cmp\Storage\Exception\FileExistsException;
use Cmp\Storage\Exception\FileNotFoundException;
use Cmp\Storage\Exception\InvalidPathException;

/**
 * Class FileSystemAdapter.
 */
class FileSystemAdapter implements \Cmp\Storage\AdapterInterface
{
    /**
     * Adapter Name.
     */
    const NAME = 'FileSystem';
    const MAX_PATH_SIZE = 255; //The major part of fs has this limit

    /**
     * Get Adapter name.
     *
     * @return string
     */
    public function getName()
    {
        return self::NAME;
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
        $path = $this->normalizePath($path);

        return file_exists($path);
    }

    /**
     * Read a file.
     *
     * @param string $path The path to the file
     *
     * @throws \Cmp\Storage\FileNotFoundException
     *
     * @return string The file contents or false on failure
     */
    public function get($path)
    {
        $path = $this->normalizePath($path);
        $this->assertNotFileExists($path);

        return file_get_contents($path);
    }

    /**
     * Retrieves a read-stream for a path.
     *
     * @param string $path The path to the file
     *
     * @throws \Cmp\Storage\FileNotFoundException
     *
     * @return resource The path resource or false on failure
     */
    public function getStream($path)
    {
        $path = $this->normalizePath($path);
        $this->assertNotFileExists($path);

        return fopen($path, 'r');
    }

    /**
     * Rename a file.
     *
     * @param string $path      Path to the existing file
     * @param string $newpath   The new path of the file
     * @param bool   $overwrite
     *
     * @return bool Thrown if $newpath exists
     *
     * @throws FileExistsException
     */
    public function rename($path, $newpath, $overwrite = false)
    {
        $path = $this->normalizePath($path);
        if (!$overwrite && $this->exists($newpath)) {
            throw new FileExistsException($newpath);
        }
        $this->assertNotFileExists($path);

        return rename($path, $newpath);
    }

    /**
     * Delete a file or directory.
     *
     * @param string $path
     *
     * @return bool True on success, false on failure
     */
    public function delete($path)
    {
        $path = $this->normalizePath($path);
        if (!file_exists($path)) {
            return false;
        }

        if (is_dir($path)) {
            return $this->removeDirectory($path);
        } else {
            return unlink($path);
        }
    }

    /**
     * Create a file or update if exists. It will create the missing folders.
     *
     * @param string $path     The path to the file
     * @param string $contents The file contents
     *
     * @return bool True on success, false on failure
     *
     * @throws InvalidPathException
     */
    public function put($path, $contents)
    {
        $this->assertFileMaxLength($path);
        if (is_dir($path)) {
            throw new InvalidPathException($path);
        }
        if (!$this->createParentFolder($path)) {
            throw new InvalidPathException($path);
        }
        if (($size = file_put_contents($path, $contents)) === false) {
            return false;
        }

        return true;
    }

    /**
     * Create a file or update if exists. It will create the missing folders.
     *
     * @param string   $path     The path to the file
     * @param resource $resource The file handle
     *
     * @return bool
     *
     * @throws InvalidPathException
     */
    public function putStream($path, $resource)
    {
        $this->assertFileMaxLength($path);
        if (is_dir($path)) {
            throw new InvalidPathException($path);
        }
        if (!$this->createParentFolder($path)) {
            throw new InvalidPathException($path);
        }
        $stream = fopen($path, 'w+');

        if (!$stream) {
            return false;
        }

        stream_copy_to_stream($resource, $stream);

        return fclose($stream);
    }

    /**
     * @param $path
     *
     * @throws FileNotFoundException
     */
    private function assertNotFileExists($path)
    {
        if (!$this->exists($path) || !is_file($path)) {
            throw new FileNotFoundException($path);
        }
    }

    private function normalizePath($path)
    {
        $this->assertFileMaxLength($path);

        return realpath($path);
    }

    /**
     * @param $path
     *
     * @return bool
     */
    private function createParentFolder($path)
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            return mkdir($dir, 0777, true);
        }

        return true;
    }

    /**
     * @param $path
     *
     * @throws InvalidPathException
     */
    private function assertFileMaxLength($path)
    {
        if (strlen(basename($path)) > self::MAX_PATH_SIZE) {
            throw new InvalidPathException($path);
        }
    }

    /**
     * Removes directory recursively.
     *
     * @param string $path
     *
     * @return bool
     */
    private function removeDirectory($path)
    {
        if (is_dir($path)) {
            $objects = scandir($path);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (is_dir($path.'/'.$object)) {
                        if (!$this->removeDirectory($path.'/'.$object)) {
                            return false;
                        }
                    } else {
                        if (!unlink($path.'/'.$object)) {
                            return false;
                        }
                    }
                }
            }

            return rmdir($path);
        }
    }
}

<?php
namespace Cmp\Storage\Adapter;


use Cmp\Storage\Exception\FileNotFoundException;
use Cmp\Storage\Exception\InvalidPathException;

class FileSystemAdapter implements \Cmp\Storage\AdapterInterface
{


    /**
     * Get Adapter name
     *
     * @return string
     */
    public function getName()
    {
        return "FileSystem";
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
     * @param string $path The path to the file.
     *
     * @throws \Cmp\Storage\FileNotFoundException
     *
     * @return string The file contents or false on failure.
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
     * @param string $path The path to the file.
     *
     * @throws \Cmp\Storage\FileNotFoundException
     *
     * @return resource The path resource or false on failure.
     */
    public function getStream($path)
    {
        $path = $this->normalizePath($path);
        $this->assertNotFileExists($path);

        return fopen($path, "r");

    }

    /**
     * Rename a file.
     *
     * @param string $path    Path to the existing file.
     * @param string $newpath The new path of the file.
     *
     * @throws \Cmp\Storage\FileExistsException   Thrown if $newpath exists.
     * @throws \Cmp\Storage\FileNotFoundException Thrown if $path does not exist.
     *
     * @return bool True on success, false on failure.
     */
    public function rename($path, $newpath)
    {
        $path = $this->normalizePath($path);
        $this->assertNotFileExists($path);
        $newpath = $this->normalizePath($newpath);

        return rename($path, $newpath);
    }

    /**
     * Delete a file.
     *
     * @param string $path
     *
     * @throws \Cmp\Storage\FileNotFoundException
     *
     * @return bool True on success, false on failure.
     */
    public function delete($path)
    {
        $path = $this->normalizePath($path);
        $this->assertNotFileExists($path);

        return unlink($path);
    }

    /**
     * Create a file or update if exists. It will create the missing folders.
     *
     * @param string $path     The path to the file.
     * @param string $contents The file contents.
     *
     * @return bool True on success, false on failure.
     * @throws InvalidPathException
     */
    public function put($path, $contents)
    {

     echo $path;

        if (is_dir($path)) {
            throw new InvalidPathException();
        }

        //create ancestors
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if (($size = file_put_contents($path, $contents)) === false) {
            return false;
        }


        return true;
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
        $stream = fopen($path, 'w+');

        if (!$stream) {
            return false;
        }

        stream_copy_to_stream($resource, $stream);

        if (!fclose($stream)) {
            return false;
        }

        return true;
    }

    /**
     * @param $path
     *
     * @throws FileNotFoundException
     */
    private function assertNotFileExists($path)
    {
        if (!$this->exists($path) || !is_file($path)) {
            throw new FileNotFoundException();
        }
    }

    private function normalizePath($path)
    {
        return realpath($path);
    }


}
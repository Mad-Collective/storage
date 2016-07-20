<?php

namespace Cmp\Storage\Adapter;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Cmp\Storage\AdapterInterface;
use Cmp\Storage\Exception\InvalidStorageAdapterException;

/**
 * Class S3AWSAdapter
 *
 * @package Cmp\Storage\Adapter
 */
class S3AWSAdapter implements AdapterInterface
{
    /**
     * @var S3Client
     */
    private $client;
    /**
     * @var string
     */
    private $bucket;

    /**
     * @var array
     */
    private static $mandatoryEnvVars = [
        'AWS_REGION',
        'AWS_ACCESS_KEY_ID',
        'AWS_SECRET_ACCESS_KEY',
        'AWS_BUCKET'
    ];

    /**
     * S3AWSAdapter constructor.
     *
     * @param array  $config
     * @param string $bucket
     *
     * @throws InvalidStorageAdapterException
     */
    public function __construct(array $config = [], $bucket = "")
    {
        if (empty($config) || empty($bucket)) {
            $this->assertMandatoryConfigEnv();
            $config = $this->getConfigFromEnv();
            $this->bucket = getenv('AWS_BUCKET');
        }
        $this->client = new S3Client($config);
    }

    /**
     * Get Adapter name
     *
     * @return string
     */
    public function getName()
    {
        return "S3AWS";
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
        if ($this->client->doesObjectExist($this->bucket, $path)) {
            return true;
        }

        return $this->doesDirectoryExist($path);
    }


    /**
     * Read a file.
     *
     * @param string $path The path to the file.
     *
     * @throws FileNotFoundException
     *
     * @return string The file contents or false on failure.
     */
    public function get($path)
    {
        $response = $this->readObject($path);

        if ($response !== false) {
            $response = $response['Body']->getContents();
        }

        return $response;
    }

    /**
     * Retrieves a read-stream for a path.
     *
     * @param string $path The path to the file.
     *
     * @throws FileNotFoundException
     *
     * @return resource The path resource or false on failure.
     */
    public function getStream($path)
    {

        $response = $this->readObject($path);

        if ($response !== false) {
            $response = $response['Body']->detach();
        }

        return $response;

    }

    /**
     * Read an object and normalize the response.
     *
     * @param string $path
     *
     * @return array|bool
     */
    protected function readObject($path)
    {
        $command = $this->client->getCommand(
            'getObject',
            [
                'Bucket' => $this->bucket,
                'Key' => $path,
                '@http' => [
                    'stream' => true,
                ],
            ]
        );

        try {
            /** @var Result $response */
            $response = $this->client->execute($command);
        } catch (S3Exception $e) {
            return false;
        }

        return $response;
    }

    /**
     * Rename a file.
     *
     * @param string $path    Path to the existing file.
     * @param string $newpath The new path of the file.
     *
     * @throws \Cmp\Storage\FileExistsException   Thrown if $newpath exists.
     * @throws FileNotFoundException Thrown if $path does not exist.
     *
     * @return bool True on success, false on failure.
     */
    public function rename($path, $newpath)
    {
        if (!$this->copy($path, $newpath)) {
            return false;
        }

        return $this->delete($path);

    }

    /**
     * Delete a file.
     *
     * @param string $path
     *
     * @throws FileNotFoundException
     *
     * @return bool True on success, false on failure.
     */
    public function delete($path)
    {

        $command = $this->client->getCommand(
            'deleteObject',
            [
                'Bucket' => $this->bucket,
                'Key' => $path,
            ]
        );

        try {
            $this->client->execute($command);
        } catch (S3Exception $e) {
            return false;
        }

        return true;

    }


    /**
     * Create a file or update if exists. It will create the missing folders.
     *
     * @param string          $path     The path to the file.
     * @param string|resource $contents The file contents.
     *
     * @return bool True on success, false on failure.
     * @throws \Cmp\Storage\InvalidPathException
     */
    public function put($path, $contents)
    {
        $acl = 'public-read';
        $options = [];
        try {
            $this->client->upload($this->bucket, $path, $contents, $acl, ['params' => $options]);
        } catch (S3Exception $e) {
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
        return $this->put($path, $resource);
    }


    /**
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    private function copy($path, $newpath)
    {
        $command = $this->client->getCommand(
            'copyObject',
            [
                'Bucket' => $this->bucket,
                'Key' => $newpath,
                'CopySource' => urlencode($this->bucket.'/'.$path),
                'ACL' => 'public-read',
            ]
        );

        try {
            $this->client->execute($command);
        } catch (S3Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param string $location
     *
     * @return bool
     */
    private function doesDirectoryExist($location)
    {
        $command = $this->client->getCommand(
            'listObjects',
            [
                'Bucket' => $this->bucket,
                'Prefix' => rtrim($location, '/').'/',
                'MaxKeys' => 1,
            ]
        );

        try {
            $result = $this->client->execute($command);

            return $result['Contents'] || $result['CommonPrefixes'];
        } catch (S3Exception $e) {
            if ($e->getStatusCode() === 403) {
                return false;
            }

            throw $e;
        }
    }

    /**
     * @throws InvalidStorageAdapterException
     */
    private function assertMandatoryConfigEnv()
    {
        foreach (self::$mandatoryEnvVars as $env) {
            if (empty(getenv($env))) {
                throw new InvalidStorageAdapterException(
                    'The env "'.
                    $env.
                    '" is missing. Set it to run this adapter as builtin or use the regular constructor.'
                );
            }

        }
    }

    /**
     * @return array
     */
    private function getConfigFromEnv()
    {
        $config = [
            'version' => 'latest',
            'region' => getenv('AWS_REGION'),
            'credentials' => [
                'key' => getenv('AWS_ACCESS_KEY_ID'),
                'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
            ]
        ];

        return $config;
    }
}

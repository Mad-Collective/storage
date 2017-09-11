<?php

namespace Cmp\Storage\Adapter;

use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Cmp\Storage\AdapterInterface;
use Cmp\Storage\Exception\AdapterException;
use Cmp\Storage\Exception\FileExistsException;
use Cmp\Storage\Exception\FileNotFoundException;
use Cmp\Storage\Exception\InvalidPathException;
use Cmp\Storage\Exception\InvalidStorageAdapterException;
use InvalidArgumentException;
use Mimey\MimeTypes;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

/**
 * Class S3AWSAdapter.
 */
class S3AWSAdapter implements AdapterInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    const ACL_PUBLIC_READ = 'public-read';
    /**
     * Adapter Name.
     */
    const NAME = 'S3AWS';
    /**
     * @var array
     */
    private static $mandatoryEnvVars = [
        'AWS_REGION',
        'AWS_ACCESS_KEY_ID',
        'AWS_SECRET_ACCESS_KEY',
        'AWS_BUCKET',
    ];
    /**
     * @var S3Client
     */
    private $client;
    /**
     * @var string
     */
    private $bucket;
    /**
     * @var $pathPrefix
     */
    private $pathPrefix;
    /**
     * @var MimeTypes
     */
    private $mimes;

    /**
     * S3AWSAdapter constructor.
     *
     * @param array  $config
     * @param string $bucket
     * @param string $pathPrefix
     *
     * @throws InvalidStorageAdapterException
     */
    public function __construct(array $config = [], $bucket = '', $pathPrefix = '')
    {
        $this->bucket = $bucket;
        $this->logger = new NullLogger();
        if (empty($config) || empty($bucket)) {
            $this->assertMandatoryConfigEnv();
            $config       = $this->getConfigFromEnv();
            $this->bucket = getenv('AWS_BUCKET');
        }
        $this->client     = new S3Client($config);
        $this->pathPrefix = $pathPrefix;
        $this->mimes      = new MimeTypes();
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
            'version'     => 'latest',
            'region'      => getenv('AWS_REGION'),
            'credentials' => [
                'key'    => getenv('AWS_ACCESS_KEY_ID'),
                'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
            ],
        ];

        return $config;
    }

    /**
     * Read a file.
     *
     * @param string $path The path to the file
     *
     * @throws FileNotFoundException
     *
     * @return string The file contents or false on failure
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
     * @param $path
     *
     * @return bool|Result
     *
     * @throws AdapterException
     */
    protected function readObject($path)
    {
        $path    = $this->trimPrefix($path);
        $command = $this->client->getCommand(
            'getObject',
            [
                'Bucket' => $this->bucket,
                'Key'    => $path,
                '@http'  => [
                    'stream' => true,
                ],
            ]
        );

        try {
            /** @var Result $response */
            $response = $this->client->execute($command);
        } catch (S3Exception $e) {
            $this->logger->log(
                LogLevel::ERROR,
                'Adapter "'.$this->getName().'" fails. Impossible read {path}.',
                ['exception' => $e, 'path' => $path]
            );

            return false;
        }

        return $response;
    }

    private function trimPrefix($prefix)
    {
        $from   = '/'.preg_quote($this->pathPrefix, '/').'/';
        $prefix = preg_replace($from, '', $prefix, 1);

        return ltrim($prefix, '/');
    }

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
     * Retrieves a read-stream for a path.
     *
     * @param string $path The path to the file
     *
     * @return resource The path resource or false on failure
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
     * Rename a file.
     *
     * @param string $path    Path to the existing file
     * @param string $newpath The new path of the file
     * @param bool   $overwrite
     *
     * @return bool Thrown if $newpath exists
     *
     * @throws FileExistsException
     */
    public function rename($path, $newpath, $overwrite = false)
    {
        $this->ensureWeCanWriteDestFile($newpath, $overwrite);

        if (!$this->copy($path, $newpath)) {
            return false;
        }

        return $this->delete($path);
    }

    /**
     * @param $newpath
     * @param $overwrite
     *
     * @throws FileExistsException
     */
    private function ensureWeCanWriteDestFile($newpath, $overwrite)
    {
        if (!$overwrite && $this->exists($newpath)) {
            $e = new FileExistsException($newpath);
            $this->logger->log(
                LogLevel::ERROR,
                'Adapter "'.$this->getName().'" fails. Des file {path} already exists.',
                ['exception' => $e, 'path' => $newpath]
            );

            throw $e;
        }
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
        $path = $this->trimPrefix($path);
        if ($this->client->doesObjectExist($this->bucket, $path)) {
            return true;
        }

        return $this->doesDirectoryExist($path);
    }

    /**
     * @param $path
     *
     * @return bool
     *
     * @throws AdapterException
     */
    private function doesDirectoryExist($path)
    {
        $command = $this->client->getCommand(
            'listObjects',
            [
                'Bucket'  => $this->bucket,
                'Prefix'  => $this->trimPrefix($path).'/',
                'MaxKeys' => 1,
            ]
        );

        try {
            $result = $this->client->execute($command);

            return $result['Contents'] || $result['CommonPrefixes'];
        } catch (S3Exception $e) {
            $this->logger->log(
                LogLevel::ERROR,
                'Adapter "'.$this->getName().'" fails. Impossible get information about directory {path}.',
                ['exception' => $e, 'from' => $path]
            );

            return false;
        }
    }

    /**
     * Copy a file.
     *
     * @param string $path    Path to the existing file
     * @param string $newpath The destination path of the copy
     *
     * @return bool
     */
    public function copy($path, $newpath)
    {
        $path    = $this->trimPrefix($path);
        $newpath = $this->trimPrefix($newpath);
        $command = $this->client->getCommand(
            'copyObject',
            [
                'Bucket'      => $this->bucket,
                'Key'         => $newpath,
                'CopySource'  => urlencode($this->bucket.'/'.$path),
                'ACL'         => self::ACL_PUBLIC_READ,
                'ContentType' => $this->getMimeType($path),
            ]
        );

        try {
            $this->client->execute($command);
        } catch (S3Exception $e) {
            $this->logger->log(
                LogLevel::ERROR,
                'Adapter "'.$this->getName().'" fails. Impossible copy file from {from}} to {to}.',
                ['exception' => $e, 'from' => $path, 'to' => $newpath]
            );

            return false;
        }

        return true;
    }

    private function getMimeType($path)
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        if (empty($ext)) {
            return 'text/plain';
        }

        return $this->mimes->getMimeType($ext);
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
        $path = $this->trimPrefix($path);
        try {
            if ($this->doesDirectoryExist($path)) {
                $this->client->deleteMatchingObjects($this->bucket, $path.'/');

                return true;
            } elseif ($this->client->doesObjectExist($this->bucket, $path)) {
                $this->client->deleteMatchingObjects($this->bucket, $path);

                return true;
            }
        } catch (\Exception $e) {
            $this->logger->log(
                LogLevel::ERROR,
                'Adapter "'.$this->getName().'" fails. Impossible delete file {path}.',
                ['exception' => $e, 'path' => $path]
            );

            return false;
        }

        return false;
    }

    /**
     * Create a file or update if exists. It will create the missing folders.
     *
     * @param string   $path     The path to the file
     * @param resource $resource The file handle
     *
     * @throws InvalidArgumentException Thrown if $resource is not a resource
     *
     * @return bool True on success, false on failure
     */
    public function putStream($path, $resource)
    {
        return $this->put($path, $resource);
    }

    /**
     * Create a file or update if exists. It will create the missing folders.
     *
     * @param string          $path     The path to the file
     * @param string|resource $contents The file contents
     *
     * @return bool True on success, false on failure
     *
     * @throws InvalidPathException
     */
    public function put($path, $contents)
    {
        $options = ['ContentType' => $this->getMimeType($path)];
        $path    = $this->trimPrefix($path);
        try {
            $this->client->upload($this->bucket, $path, $contents, self::ACL_PUBLIC_READ, ['params' => $options]);
        } catch (S3Exception $e) {
            $this->logger->log(
                LogLevel::ERROR,
                'Adapter "'.$this->getName().'" fails. Impossible upload a file {path}.',
                ['exception' => $e, 'path' => $path]
            );

            return false;
        }

        return true;
    }
}

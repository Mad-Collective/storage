<?php
/**
 * Created by PhpStorm.
 * User: jordimartin
 * Date: 18/07/16
 * Time: 11:26
 */

namespace Cmp\Storage\Adapter;

use Aws\S3\S3Client;
use Cmp\Storage\AdapterInterface;
use Cmp\Storage\FactoryAdapterInterface;

class S3AWSAdapter implements \Cmp\Storage\AdapterInterface
{
    /**
     * @var S3Client
     */
    private $client;
    /**
     * @var string
     */
    private $bucket;


    public function __construct()
    {
        $config = [
            'version' => 'latest',
            'region' => getenv('AWS_REGION'),
            'credentials' => [
                'key' => getenv('AWS_ACCESS_KEY_ID'),
                'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
            ]
        ];
        $this->client = new S3Client($config);
        $this->bucket = getenv('AWS_BUCKET');
    }

    /**
     * {@inheritdoc}
     */
    public function applyPathPrefix($prefix)
    {
        return ltrim($prefix, '/');
    }

    /**
     * {@inheritdoc}
     */
    public function setPathPrefix($prefix)
    {
        $prefix = ltrim($prefix, '/');

        return $prefix;
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
        $location = $this->applyPathPrefix($path);

        if ($this->s3Client->doesObjectExist($this->bucket, $location)) {
            return true;
        }

        return $this->doesDirectoryExist($location);
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
        $response = $this->readObject($path);

        if ($response !== false) {
            $response['contents'] = $response['contents']->getContents();
        }

        return $response;
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
        $response = $this->readObject($path);

        if ($response !== false) {
            $response['stream'] = $response['contents']->detach();
            unset($response['contents']);
        }

        return $response;
    }

    /**
     * Read an object and normalize the response.
     *
     * @param $path
     *
     * @return array|bool
     */
    protected function readObject($path)
    {
        $command = $this->s3Client->getCommand(
            'getObject',
            [
                'Bucket' => $this->bucket,
                'Key' => $this->applyPathPrefix($path),
                '@http' => [
                    'stream' => true,
                ],
            ]
        );

        try {
            /** @var Result $response */
            $response = $this->s3Client->execute($command);
        } catch (S3Exception $e) {
            return false;
        }

        return $this->normalizeResponse($response->toArray(), $path);
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
     * @throws \Cmp\Storage\FileNotFoundException
     *
     * @return bool True on success, false on failure.
     */
    public function delete($path)
    {
        $location = $this->applyPathPrefix($path);

        $command = $this->s3Client->getCommand(
            'deleteObject',
            [
                'Bucket' => $this->bucket,
                'Key' => $location,
            ]
        );

        $this->s3Client->execute($command);

        return !$this->has($path);
    }


    protected function normalizeResponse(array $response, $path = null)
    {
        $result = [
            'path' => $path
                ?: $this->removePathPrefix(
                    isset($response['Key']) ? $response['Key'] : $response['Prefix']
                ),
        ];
        $result = array_merge($result, Util::pathinfo($result['path']));

        if (isset($response['LastModified'])) {
            $result['timestamp'] = strtotime($response['LastModified']);
        }

        if (substr($result['path'], -1) === '/') {
            $result['type'] = 'dir';
            $result['path'] = rtrim($result['path'], '/');

            return $result;
        }

        return array_merge($result, Util::map($response, static::$resultMap), ['type' => 'file']);
    }

    /**
     * Create a file or update if exists. It will create the missing folders.
     *
     * @param string $path     The path to the file.
     * @param string $contents The file contents.
     *
     * @return bool True on success, false on failure.
     * @throws \Cmp\Storage\InvalidPathException
     */
    public function put($path, $contents)
    {
        $key = $this->applyPathPrefix($path);
        $acl = isset($options['ACL']) ? $options['ACL'] : 'private';

        if (!isset($options['ContentType']) && is_string($body)) {
            $options['ContentType'] = Util::guessMimeType($path, $body);
        }

        if (!isset($options['ContentLength'])) {
            $options['ContentLength'] = is_string($body) ? Util::contentSize($body) : Util::getStreamSize($body);
        }

        if ($options['ContentLength'] === null) {
            unset($options['ContentLength']);
        }

        $this->s3Client->upload($this->bucket, $key, $body, $acl, ['params' => $options]);

        return $this->normalizeResponse($options, $key);
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
        // TODO: Implement putStream() method.
    }


    private function copy($path, $newpath)
    {
        $command = $this->s3Client->getCommand(
            'copyObject',
            [
                'Bucket' => $this->bucket,
                'Key' => $this->applyPathPrefix($newpath),
                'CopySource' => urlencode($this->bucket.'/'.$this->applyPathPrefix($path)),
                'ACL' => $this->getRawVisibility($path) === AdapterInterface::VISIBILITY_PUBLIC
                    ? 'public-read' : 'private',
            ] + $this->options
        );

        try {
            $this->s3Client->execute($command);
        } catch (S3Exception $e) {
            return false;
        }

        return true;
    }

    private function doesDirectoryExist($location)
    {
        // Maybe this isn't an actual key, but a prefix.
        // Do a prefix listing of objects to determine.
        $command = $this->s3Client->getCommand(
            'listObjects',
            [
                'Bucket' => $this->bucket,
                'Prefix' => rtrim($location, '/').'/',
                'MaxKeys' => 1,
            ]
        );

        try {
            $result = $this->s3Client->execute($command);

            return $result['Contents'] || $result['CommonPrefixes'];
        } catch (S3Exception $e) {
            if ($e->getStatusCode() === 403) {
                return false;
            }

            throw $e;
        }
    }
}

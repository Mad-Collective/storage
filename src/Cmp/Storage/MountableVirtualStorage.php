<?php

namespace Cmp\Storage;

use Cmp\Storage\Exception\FileExistsException;

/**
 * Class MountableVirtualStorage.
 */
class MountableVirtualStorage implements VirtualStorageInterface
{
    const ROOT_PATH = '/';
    /**
     * @var MountPointSortedSet
     */
    private $mountPoints;
    private $defaultMountPoint;

    /**
     * MountableVirtualStorage constructor.
     *
     * @param VirtualStorageInterface $defaultVirtualStorage
     */
    public function __construct($defaultVirtualStorage)
    {
        $this->mountPoints = new MountPointSortedSet();
        $this->defaultMountPoint = $this->getDefaultMountPoint($defaultVirtualStorage);
        $this->registerMountPoint($this->defaultMountPoint);
    }

    public function getMountPoints()
    {
        return $this->mountPoints->getIterator();
    }

    /**
     * @param MountPoint $mountPoint
     */
    public function registerMountPoint(MountPoint $mountPoint)
    {
        $this->mountPoints->set($mountPoint);
    }

    /**
     * @param $path
     *
     * @return MountPoint
     */
    public function getMountPointForPath($path)
    {
        $it = $this->mountPoints->getIterator();
        $virtualPath = new VirtualPath($path);
        foreach ($it as $mountPoint) {
            if ($mountPoint->getVirtualPath()->isChild($virtualPath)) {
                return $mountPoint;
            }
        }

        return $this->defaultMountPoint;
    }

    public function exists($path)
    {
        return $this->getStorageForPath($path)->exists($path);
    }

    public function get($path)
    {
        return $this->getStorageForPath($path)->get($path);
    }

    public function getStream($path)
    {
        return $this->getStorageForPath($path)->getStream($path);
    }

    public function rename($path, $newpath, $overwrite = false)
    {
        $storageSrc = $this->getStorageForPath($path);
        $storageDst = $this->getStorageForPath($newpath);

        if (!$storageSrc->exists($path)) {
            return false;
        }

        if (!$overwrite && $storageDst->exists($newpath)) {
            throw new FileExistsException($newpath);
        }

        $stream = $storageSrc->getStream($path);
        if (!$stream) {
            return false;
        }
        $storageDst->putStream($newpath, $stream);

        return $storageDst->exists($newpath) && $storageSrc->delete($path);
    }

    public function delete($path)
    {
        return $this->getStorageForPath($path)->delete($path);
    }

    public function put($path, $contents)
    {
        return $this->getStorageForPath($path)->put($path, $contents);
    }

    public function putStream($path, $resource)
    {
        return $this->getStorageForPath($path)->putStream($path, $resource);
    }

    /**
     * @param $defaultVirtualStorage
     *
     * @return MountPoint
     */
    private function getDefaultMountPoint(VirtualStorageInterface $defaultVirtualStorage)
    {
        $defaultMountPoint = new MountPoint(self::ROOT_PATH, $defaultVirtualStorage);

        return $defaultMountPoint;
    }

    /**
     * @param $path
     *
     * @return VirtualStorageInterface
     */
    private function getStorageForPath($path)
    {
        $mountPoint = $this->getMountPointForPath($path);

        return $mountPoint->getStorage();
    }
}

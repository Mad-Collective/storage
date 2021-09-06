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
        $this->mountPoints       = new MountPointSortedSet();
        $this->defaultMountPoint = $this->getDefaultMountPoint($defaultVirtualStorage);
        $this->registerMountPoint($this->defaultMountPoint);
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
     * @param MountPoint $mountPoint
     */
    public function registerMountPoint(MountPoint $mountPoint)
    {
        $this->mountPoints->set($mountPoint);
    }

    public function getMountPoints()
    {
        return $this->mountPoints->getIterator();
    }

    public function exists($path)
    {
        $vp = new VirtualPath($path);

        return $this->getStorageForPath($vp)->exists($vp->getPath());
    }

    /**
     * @param VirtualPath $vp
     *
     * @return VirtualStorageInterface
     */
    private function getStorageForPath(VirtualPath $vp)
    {
        $mountPoint = $this->getMountPointForPath($vp);

        return $mountPoint->getStorage();
    }

    /**
     * @param VirtualPath $vp
     *
     * @return MountPoint
     *
     */
    public function getMountPointForPath(VirtualPath $vp)
    {
        $it = $this->mountPoints->getIterator();

        foreach ($it as $mountPoint) {
            if ($mountPoint->getVirtualPath()->isChild($vp)) {
                return $mountPoint;
            }
        }

        return $this->defaultMountPoint;
    }

    public function get($path)
    {
        $vp = new VirtualPath($path);

        return $this->getStorageForPath($vp)->get($vp->getPath());
    }

    public function getStream($path)
    {
        $vp = new VirtualPath($path);

        return $this->getStorageForPath($vp)->getStream($vp->getPath());
    }

    public function rename($path, $newpath, $overwrite = false)
    {
        $svp        = new VirtualPath($path);
        $dvp        = new VirtualPath($newpath);
        $storageSrc = $this->getStorageForPath($svp);
        $storageDst = $this->getStorageForPath($dvp);

        if (!$overwrite && $storageDst->exists($dvp->getPath())) {
            throw new FileExistsException($dvp->getPath());
        }

        return $this->copy($svp->getPath(), $dvp->getPath()) && $storageSrc->delete($svp->getPath());
    }

    public function copy($path, $newpath)
    {
        $svp        = new VirtualPath($path);
        $dvp        = new VirtualPath($newpath);
        $storageSrc = $this->getStorageForPath($svp);
        $storageDst = $this->getStorageForPath($dvp);

        if (!$storageSrc->exists($svp->getPath())) {
            return false;
        }

        $stream = $storageSrc->getStream($svp->getPath());
        if (!$stream) {
            return false;
        }
        $storageDst->putStream($dvp->getPath(), $stream);

        return $storageDst->exists($dvp->getPath());
    }

    public function delete($path)
    {
        $vp = new VirtualPath($path);

        return $this->getStorageForPath($vp)->delete($vp->getPath());
    }

    public function put($path, $contents)
    {
        $vp = new VirtualPath($path);

        return $this->getStorageForPath($vp)->put($vp->getPath(), $contents);
    }

    public function putStream($path, $resource)
    {
        $vp = new VirtualPath($path);

        return $this->getStorageForPath($vp)->putStream($vp->getPath(), $resource);
    }
}

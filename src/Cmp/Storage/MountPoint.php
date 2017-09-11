<?php

namespace Cmp\Storage;

/**
 * Class MountPoint.
 */
class MountPoint
{
    /**
     * @var string
     */
    private $virtualPath;
    /**
     * @var VirtualStorageInterface
     */
    private $virtualStorage;

    /**
     * MountPoint constructor.
     *
     * @param string                  $path
     * @param VirtualStorageInterface $virtualStorage
     */
    public function __construct($path, VirtualStorageInterface $virtualStorage)
    {
        $this->virtualPath    = new VirtualPath($path);
        $this->virtualStorage = $virtualStorage;
    }

    /**
     * @return VirtualStorageInterface
     */
    public function getStorage()
    {
        return $this->virtualStorage;
    }

    /**
     * @return VirtualPath
     */
    public function getVirtualPath()
    {
        return $this->virtualPath;
    }
}

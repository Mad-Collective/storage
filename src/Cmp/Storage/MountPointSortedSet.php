<?php

namespace Cmp\Storage;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * Class MountPointHeap.
 */
class MountPointSortedSet implements IteratorAggregate
{
    /**
     * @var array
     */
    private $mountPoints;

    /**
     * MountPointSortedSet constructor.
     */
    public function __construct()
    {
        $this->mountPoints = [];
    }

    /**
     * @param MountPoint $value
     */
    public function set(MountPoint $value)
    {
        $this->mountPoints[$value->getVirtualPath()->getPath()] = $value;
        $this->sort();
    }

    /**
     * @return bool
     */
    private function sort()
    {
        return uksort($this->mountPoints, [$this, 'compare']);
    }

    /**
     * @param $path
     *
     * @return MountPoint|bool
     */
    public function get($path)
    {
        if (!$this->contains($path)) {
            return false;
        }

        return $this->mountPoints[$path];
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public function contains($path)
    {
        return isset($this->mountPoints[$path]);
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public function remove($path)
    {
        if (!$this->contains($path)) {
            return false;
        }

        unset($this->mountPoints[$path]);

        return true;
    }

    /**
     * Retrieve an external iterator.
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     *
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     *                     <b>Traversable</b>
     *
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new ArrayIterator($this->mountPoints);
    }

    /**
     * @param string $value1
     * @param string $value2
     *
     * @return int
     */
    private function compare($value1, $value2)
    {
        $s1 = substr_count($value1, '/');
        $s2 = substr_count($value2, '/');

        if ($s1 == $s2) {
            return strcmp($value2, $value1);
        }

        return $s2 - $s1;
    }
}

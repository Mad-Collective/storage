<?php

namespace Cmp\Storage\Strategy;

use Cmp\Storage\VirtualStorageInterface;

interface StrategyInterface extends VirtualStorageInterface
{
    /**
     * Return the Strategy name.
     *
     * @return string name
     */
    public function getStrategyName();

    /**
     * @return VirtualStorageInterface[]
     */
    public function getAdapters();
}

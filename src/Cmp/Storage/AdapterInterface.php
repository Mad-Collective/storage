<?php

namespace Cmp\Storage;

/**
 * Interface AdapterInterface
 *
 * @package Cmp\Storage
 */
interface AdapterInterface extends VirtualStorageInterface
{
    /**
     * Get Adapter name
     *
     * @return string
     */
    public function getName();
}

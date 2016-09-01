<?php

namespace Cmp\Storage;

/**
 * Interface AdapterInterface.
 */
interface AdapterInterface extends VirtualStorageInterface
{
    /**
     * Get Adapter name.
     *
     * @return string
     */
    public function getName();
}

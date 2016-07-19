<?php

namespace Cmp\Storage;


interface AdapterInterface extends VirtualStorageInterface
{
    /**
     * Get Adapter name
     *
     * @return string
     */
    public function getName();

}
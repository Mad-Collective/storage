<?php

namespace Cmp\Storage\Exception;

use Exception;

/**
 * Class StorageAdapterNotFoundException
 *
 * @package Cmp\Storage\Exception
 */
class StorageAdapterNotFoundException extends Exception
{
    const CODE = 1006;

    /**
     * StorageAdapterNotFoundException constructor.
     *
     * @param string         $key
     * @param Exception|null $previous
     */
    public function __construct($key, Exception $previous = null)
    {
        parent::__construct("Storage adapter not found", $previous);
    }
}

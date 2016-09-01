<?php

namespace Cmp\Storage\Exception;

use Exception;

/**
 * Class InvalidStorageAdapterException.
 */
class InvalidStorageAdapterException extends StorageException
{
    const CODE = 1004;
    /**
     * InvalidStorageAdapterException constructor.
     *
     * @param string         $msg
     * @param Exception|null $previous
     */
    public function __construct($msg, Exception $previous = null)
    {
        parent::__construct($msg, self::CODE, $previous);
    }
}

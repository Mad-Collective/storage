<?php

namespace Cmp\Storage\Exception;

use Exception;

/**
 * Class StorageException.
 */
class StorageException extends Exception
{
    public function __construct($msg, $code, Exception $previous = null)
    {
        parent::__construct($msg, $code, $previous);
    }
}

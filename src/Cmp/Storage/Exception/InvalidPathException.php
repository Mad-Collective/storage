<?php

namespace Cmp\Storage\Exception;

use Exception;

/**
 * Class InvalidPathException
 *
 * @package Cmp\Storage\Exception
 */
class InvalidPathException extends StorageException
{

    const CODE = 1003;

    /**
     * InvalidPathException constructor.
     *
     * @param string         $path
     * @param Exception|null $previous
     */
    public function __construct($path, Exception $previous = null)
    {
        parent::__construct("Invalid '$path'",self::CODE, $previous);
    }

}

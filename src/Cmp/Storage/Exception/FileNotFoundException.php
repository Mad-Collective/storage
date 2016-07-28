<?php

namespace Cmp\Storage\Exception;

use Exception;

/**
 * Class FileNotFoundException
 *
 * @package Cmp\Storage\Exception
 */
class FileNotFoundException extends Exception
{

    const CODE = 1002;

    /**
     * FileNotFoundException constructor.
     *
     * @param string         $fileName
     * @param Exception|null $previous
     */
    public function __construct($fileName, Exception $previous = null)
    {
        parent::__construct("The file '$fileName' doesn't exist", $previous);
    }

}

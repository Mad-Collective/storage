<?php
namespace Cmp\Storage\Exception;

use Exception;

/**
 * Class FileExistsException
 *
 * @package Cmp\Storage\Exception
 */
class FileExistsException extends Exception
{

    const CODE = 1001;

    /**
     * FileNotFoundException constructor.
     *
     * @param string         $fileName
     * @param Exception|null $previous
     */
    public function __construct($fileName, Exception $previous = null)
    {
        parent::__construct("The file '$fileName' already exists", $previous);
    }

}

<?php
namespace Cmp\Storage\Exception;

use Exception;


/**
 * Class StorageException
 *
 * @package Cmp\Storage\Exception
 */
class StorageException extends Exception
{


    public function __construct($msg, $code, Exception $previous = null)
    {
        parent::__construct($msg, $code, $previous);
    }

}

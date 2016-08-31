<?php

namespace Cmp\Storage\Exception;

use Exception;

/**
 * Class StorageAdapterNotFoundException.
 */
class RelativePathNotAllowed extends StorageException
{
    const CODE = 1009;

    /**
     * StorageAdapterNotFoundException constructor.
     *
     * @param string         $key
     * @param Exception|null $previous
     */
    public function __construct($key, Exception $previous = null)
    {
        parent::__construct("Relative path not allowed $key", self::CODE, $previous);
    }
}

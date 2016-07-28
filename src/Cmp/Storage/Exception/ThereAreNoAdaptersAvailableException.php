<?php

namespace Cmp\Storage\Exception;

use Exception;

/**
 * Class ThereAreNoAdaptersAvailableException
 *
 * @package Cmp\Storage\Exception
 */
class ThereAreNoAdaptersAvailableException extends Exception
{
    const CODE = 1007;

    /**
     * ThereAreNoAdaptersAvailableException constructor.
     *
     * @param string         $key
     * @param Exception|null $previous
     */
    public function __construct($key, Exception $previous = null)
    {
        parent::__construct("There are no adapters available to use", $previous);
    }
}

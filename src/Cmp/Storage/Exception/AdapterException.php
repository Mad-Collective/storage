<?php
/**
 * Created by PhpStorm.
 * User: jordimartin
 * Date: 04/08/16
 * Time: 13:55
 */

namespace Cmp\Storage\Exception;


use Exception;

/**
 * Class AdapterException
 *
 * @package Cmp\Storage\Exception
 */
class AdapterException extends StorageException
{
    /**
     *
     */
    const CODE = 1008;


    /**
     * AdapterException constructor.
     *
     * @param string    $adapterName
     * @param Exception $previous
     */
    public function __construct($adapterName, Exception $previous){
        parent::__construct("Exception form Adapter $adapterName", self::CODE, $previous);
    }

}
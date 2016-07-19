<?php
namespace Cmp\Storage;

/**
 * Created by PhpStorm.
 * User: jordimartin
 * Date: 08/07/16
 * Time: 10:40
 */
interface FactoryAdapterInterface
{
    /**
     * @param array $config
     *
     * @return AdapterInterface
     */
    public static function create(array $config);
}

<?php

namespace Cmp\Storage;

/**
 * Interface FactoryAdapterInterface
 *
 * @package Cmp\Storage
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

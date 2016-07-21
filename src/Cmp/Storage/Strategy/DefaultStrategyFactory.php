<?php
/**
 * Created by PhpStorm.
 * User: jordimartin
 * Date: 21/07/16
 * Time: 12:02
 */

namespace Cmp\Storage\Strategy;


class DefaultStrategyFactory
{

    public static function create()
    {
        return new CallAllStrategy();
    }

}
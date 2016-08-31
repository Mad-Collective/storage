<?php

namespace Cmp\Storage\Strategy;

class DefaultStrategyFactory
{
    public static function create()
    {
        return new CallAllStrategy();
    }
}

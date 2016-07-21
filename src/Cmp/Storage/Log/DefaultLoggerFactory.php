<?php

namespace Cmp\Storage\Log;


use Cmp\Storage\Date\DefaultDateProvider;

class DefaultLoggerFactory
{
    /**
     * @return DefaultLogger
     */
    public static function create()
    {
        return new DefaultLogger(new StdOutputLogger(), new DefaultDateProvider());
    }
}
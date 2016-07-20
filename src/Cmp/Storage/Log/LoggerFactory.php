<?php

namespace Cmp\Storage\Log;


use Cmp\Storage\Date\DefaultDateProvider;

class LoggerFactory
{
    /**
     * @return DefaultLogger
     */
    public static function create()
    {
        return new DefaultLogger(new StdOutputLogger(), new DefaultDateProvider());
    }
}
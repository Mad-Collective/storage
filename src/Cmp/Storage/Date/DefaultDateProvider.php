<?php

namespace Cmp\Storage\Date;


use DateTime;
use DateTimeZone;

class DefaultDateProvider implements DateProviderInterface
{


    public function __construct()
    {
        if (empty(ini_get('date.timezone'))){
            date_default_timezone_set('UTC');
        }
    }

    /**
     * @param string $format
     *
     * @return string
     */
    public function getDate($format)
    {
        $dt = new DateTime("now", new DateTimeZone('UTC'));
        return $dt->format($format);
    }
}
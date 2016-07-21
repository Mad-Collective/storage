<?php

namespace Cmp\Storage\Date;


class DefaultDateProvider implements DateProviderInterface
{

    /**
     * @param string $format
     * @return mixed
     */
    public function getDate($format, $timezone = 'UTC')
    {
        date_default_timezone_set($timezone);
        return date($format);
    }
}
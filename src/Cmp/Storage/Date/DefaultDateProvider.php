<?php

namespace Cmp\Storage\Date;


class DefaultDateProvider implements DateProviderInterface
{

    /**
     * @param string $format
     * @return mixed
     */
    public function getDate($format)
    {
        return date($format);
    }
}
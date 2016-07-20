<?php

namespace Cmp\Storage\Date;


interface DateProviderInterface
{
    /**
     * @param string $format
     * @return mixed
     */
    public function getDate($format);
}
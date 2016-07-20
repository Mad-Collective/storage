<?php
/**
 * Created by PhpStorm.
 * User: marclopez
 * Date: 20/07/16
 * Time: 18:17
 */

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
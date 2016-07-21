<?php

namespace Cmp\Storage\Log;


Interface LogWriterInterface
{
    /**
     * @param string $message
     * @return mixed
     */
    public function write($message);
}
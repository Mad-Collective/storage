<?php

namespace Cmp\Storage\Log;


class StdOutputLogger implements LogWriterInterface
{
    /**
     * @param string $message
     */
    public function write($message)
    {
        file_put_contents('php://stdout', $message, FILE_APPEND);
    }
}
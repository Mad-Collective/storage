<?php

namespace Cmp\Storage\Adapter;

use Cmp\Storage\Exception\FileExistsException;
use Psr\Log\LogLevel;

trait LogicalChecksTrait
{

    /**
     * @param $newpath
     * @param $overwrite
     *
     * @throws FileExistsException
     */
    private function ensureWeCanWriteDestFile($newpath, $overwrite)
    {
        if (!$overwrite && $this->exists($newpath)) {
            $e = new FileExistsException($newpath);
            $this->logger->log(
                LogLevel::ERROR,
                'Adapter "'.$this->getName().'" fails. Des file {path} already exists.',
                ['exception' => $e, 'path' => $newpath]
            );

            throw $e;
        }
    }

}
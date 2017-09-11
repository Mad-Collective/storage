<?php
/**
 * Created by PhpStorm.
 * User: jordimartin
 * Date: 11/09/2017
 * Time: 12:49
 */

namespace Cmp\Storage\Strategy;

use Cmp\Storage\Exception\AdapterException;
use Cmp\Storage\Exception\FileNotFoundException;
use Psr\Log\LogLevel;

trait RunAndLogTrait
{

    /**
     * Executes the operation in all adapters, returning on the first success or false if at least one executed the
     * operation without raising exceptions.
     *
     * @param callable $fn
     *
     * @return mixed If all adapters raised exceptions, the first one will be thrown
     *
     * @throws AdapterException|\Exception
     */
    private function runChain(callable $fn)
    {
        $firstException = false;
        $call           = false;
        $result         = false;
        foreach ($this->getAdapters() as $adapter) {
            try {
                $result = $fn($adapter);
                $call   = true;
                if ($result !== false) {
                    return $result;
                }
            } catch (\Exception $exception) {
                if (!$firstException) {
                    $firstException = $exception;
                }
                $this->logAdapterException($adapter, $exception);
            }
        }

        // Result will be set if at least one adapters executed the operation without exceptions
        if ($call) {
            return $result;
        }

        throw $firstException;
    }

    /**
     * @param \Cmp\Storage\VirtualStorageInterface $adapter
     * @param \Exception                           $e
     */
    private function logAdapterException($adapter, $e)
    {
        $this->logger->log(
            LogLevel::ERROR,
            'Adapter "'.$adapter->getName().'" fails.',
            ['exception' => $e]
        );
    }

    /**
     * @param       $result
     * @param       $msg
     * @param array $context
     *
     * @return mixed
     */
    private function logOnFalse($result, $msg, array $context = [])
    {
        if ($result) {
            return $result;
        }

        $this->logger->log(LogLevel::ERROR, $msg, $context);

        return $result;
    }

    /**
     * @param callable $fn
     *
     * @return bool
     */
    private function runAll(callable $fn)
    {
        $result = false;

        foreach ($this->getAdapters() as $adapter) {
            try {
                $result = $fn($adapter) || $result;
            } catch (\Exception $e) {
                $this->logAdapterException($adapter, $e);
            }
        }

        return $result;
    }

    /**
     * Gets one file from all the adapters.
     *
     * @param callable $fn
     * @param string   $path
     *
     * @return mixed
     *
     * @throws AdapterException|\Exception
     */
    private function runOne(callable $fn, $path)
    {
        $defaultException = new FileNotFoundException($path);
        foreach ($this->getAdapters() as $adapter) {
            try {
                $file = $fn($adapter);
                if ($file !== false) {
                    return $file;
                }
            } catch (\Exception $exception) {
                $defaultException = new AdapterException($path, $exception);
                $this->logAdapterException($adapter, $exception);
            }
        }

        throw $defaultException;
    }
}

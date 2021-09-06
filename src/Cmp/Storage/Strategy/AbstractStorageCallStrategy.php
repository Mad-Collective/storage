<?php

namespace Cmp\Storage\Strategy;

use Cmp\Storage\AdapterInterface;
use Cmp\Storage\VirtualStorageInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

/**
 * Class AbstractStorageCallStrategy.
 */
abstract class AbstractStorageCallStrategy implements VirtualStorageInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $adapters;

    public function __construct()
    {
        $this->adapters = [];
        $this->logger   = new NullLogger();
    }

    /**
     * @return VirtualStorageInterface[]
     */
    public function getAdapters()
    {
        return $this->adapters;
    }

    public function setAdapters(array $adapters)
    {
        $this->adapters = [];
        foreach ($adapters as $adapter) {
            $this->addAdapter($adapter);
        }
    }

    public function addAdapter(AdapterInterface $adapter)
    {
        $this->logger->log(
            LogLevel::INFO,
            'Add adapter "{adapter}" to strategy "{strategy}".',
            ['adapter' => $adapter->getName(), 'strategy' => $this->getStrategyName()]
        );
        $this->adapters[] = $adapter;
    }

    abstract public function getStrategyName();
}

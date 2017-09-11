<?php

namespace Cmp\Storage;

use Cmp\Storage\Adapter\FileSystemAdapter;
use Cmp\Storage\Exception\InvalidStorageAdapterException;
use Cmp\Storage\Strategy\AbstractStorageCallStrategy;
use Cmp\Storage\Strategy\DefaultStrategyFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

/**
 * Class StorageBuilder.
 */
class StorageBuilder implements LoggerAwareInterface
{
    /**
     * @var
     */
    private $strategy;
    /**
     * @var
     */
    private $logger;
    /**
     * @var array
     */
    private $adapters;

    /**
     * StorageBuilder constructor.
     */
    public function __construct()
    {
        $this->adapters = [];
        $this->logger   = new NullLogger();
    }

    /**
     * Build the virtual storage.
     *
     * @param                 $callStrategy
     *
     * @return VirtualStorageInterface
     *
     * @throws InvalidStorageAdapterException
     */
    public function build(AbstractStorageCallStrategy $callStrategy = null)
    {
        if (!$this->hasLoadedAdapters()) {
            $this->addAdapter($this->getDefaultBuiltinAdapter());
        }

        if ($callStrategy != null) {
            $this->setStrategy($callStrategy);
        }

        $this->bindAdaptersLogger();

        return $this->createStrategy();
    }

    /**
     * Check if one or more adapters has been loaded.
     *
     * @return bool
     */
    public function hasLoadedAdapters()
    {
        return !empty($this->adapters);
    }

    /**
     * Add a new adapter.
     *
     * @param   $adapter
     *
     * @return $this
     *
     * @throws InvalidStorageAdapterException
     */
    public function addAdapter($adapter)
    {
        if ($adapter instanceof AdapterInterface) {
            $this->registerAdapter($adapter);

            return $this;
        }

        throw new InvalidStorageAdapterException('Invalid storage adapter.');
    }

    /**
     * @param AdapterInterface $adapter
     */
    private function registerAdapter(AdapterInterface $adapter)
    {
        $this->adapters[] = $adapter;
        $this->logger->log(LogLevel::INFO, 'Added new adapter {adapter}', ['adapter' => $adapter->getName()]);
    }

    private function getDefaultBuiltinAdapter()
    {
        return new FileSystemAdapter();
    }

    private function bindAdaptersLogger()
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter instanceof LoggerAwareInterface) {
                $adapter->setLogger($this->logger);
            }
        }
    }

    /**
     * @return AbstractStorageCallStrategy
     */
    private function createStrategy()
    {
        $strategy = $this->getStrategy();
        $strategy->setLogger($this->logger);
        $strategy->setAdapters($this->adapters);
        $this->logger->log(
            LogLevel::INFO,
            'Creating strategy {strategy}',
            ['strategy' => $strategy->getStrategyName()]
        );

        return $strategy;
    }

    /**
     * Get the current strategy.
     *
     * @return AbstractStorageCallStrategy
     */
    public function getStrategy()
    {
        if ($this->strategy == null) {
            return $this->getDefaultCallStrategy();
        }

        return $this->strategy;
    }

    /**
     * Set a custom strategy.
     *
     * @param AbstractStorageCallStrategy $strategy
     *
     * @return $this
     */
    public function setStrategy(AbstractStorageCallStrategy $strategy)
    {
        $this->logger->log(
            LogLevel::INFO,
            'Set the strategy {strategy}',
            ['strategy' => $strategy->getStrategyName()]
        );
        $this->strategy = $strategy;

        return $this;
    }

    /**
     * @return AbstractStorageCallStrategy
     */
    private function getDefaultCallStrategy()
    {
        return DefaultStrategyFactory::create();
    }

    /**
     * Get the current Logger.
     *
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Set custom logger.
     *
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }
}

<?php

namespace Cmp\Storage;

use Cmp\Storage\Adapter\FileSystemAdapter;
use Cmp\Storage\Exception\InvalidStorageAdapterException;
use Cmp\Storage\Exception\StorageAdapterNotFoundException;
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
     * @var array
     */
    private static $builtinAdapters = [];
    /**
     * @var bool
     */
    private static $builtInAdaptersLoaded = false;
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
     * @throws StorageAdapterNotFoundException
     */
    public function addAdapter($adapter)
    {
        if (is_string($adapter)) {
            $this->addBuiltinAdapters();
            $this->assertBuiltInAdapterExists($adapter);
            $this->registerAdapter(self::$builtinAdapters[$adapter]);

            return $this;
        }

        if ($adapter instanceof AdapterInterface) {
            $this->registerAdapter($adapter);

            return $this;
        }

        throw new InvalidStorageAdapterException('Invalid storage adapter: '.get_class($adapter));
    }

    /**
     * @return $this
     */
    private function addBuiltinAdapters()
    {
        if (!self::$builtInAdaptersLoaded) {
            self::$builtInAdaptersLoaded = true;
            foreach (glob(__DIR__.DIRECTORY_SEPARATOR.'Adapter'.DIRECTORY_SEPARATOR.'*.php') as $adapterFileName) {
                $className = __NAMESPACE__.'\\'.'Adapter'.'\\'.basename($adapterFileName, '.php');
                try {
                    $class                                    = new $className();
                    self::$builtinAdapters[$class->getName()] = $class;
                } catch (\Exception $e) {
                    $this->logger->log(
                        LogLevel::INFO,
                        'Impossible start {className} client',
                        ['className' => $className]
                    );
                }
            }
        }

        return $this;
    }

    /**
     * @param $adapter
     *
     * @throws StorageAdapterNotFoundException
     */
    private function assertBuiltInAdapterExists($adapter)
    {
        if (!array_key_exists($adapter, self::$builtinAdapters)) {
            throw new StorageAdapterNotFoundException("Builtin storage \"$adapter\" not found");
        }
    }

    /**
     * @param AdapterInterface $adapter
     */
    private function registerAdapter(AdapterInterface $adapter)
    {
        if ($this->logger && $adapter instanceof LoggerAwareInterface) {
            $adapter->setLogger($this->logger);
        }
        $this->adapters[] = $adapter;
        $this->logger->log(LogLevel::INFO, 'Added new adapter {adapter}', ['adapter' => $adapter->getName()]);
    }

    private function getDefaultBuiltinAdapter()
    {
        return FileSystemAdapter::NAME;
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

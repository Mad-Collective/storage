<?php
namespace Cmp\Storage\Strategy;

use Cmp\Storage\AdapterInterface;
use Cmp\Storage\VirtualStorageInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use SplDoublyLinkedList;

/**
 * Class AbstractStorageCallStrategy
 *
 * @package Cmp\Storage\Strategy
 */
abstract class AbstractStorageCallStrategy implements VirtualStorageInterface, LoggerAwareInterface
{


    /**
     * @var LoggerInterface
     */
    private $logger;
    private $splList;


    public function __construct()
    {
        $this->initList();
    }

    public function addAdapter(AdapterInterface $adapter)
    {
        $this->splList->push($adapter);
    }

    public function addAdapters(array $adapters)
    {
        $this->initList();
        foreach ($adapters as $adapter) {
            $this->addAdapter($adapter);
        }
    }

    /**
     * @return \SplStack
     */
    public function getAdapters()
    {
        return $this->splList;
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        if (!$this->logger) {
            return;
        }
        $this->logger->log($level, $message, $context);
    }

    abstract public function getStrategyName();

    private function initList()
    {
        $this->splList = new \SplDoublyLinkedList();
        $this->splList->setIteratorMode(SplDoublyLinkedList::IT_MODE_FIFO);
    }
}

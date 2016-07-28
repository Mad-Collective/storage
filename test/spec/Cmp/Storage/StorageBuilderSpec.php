<?php

namespace spec\Cmp\Storage;

use Cmp\Storage\AdapterInterface;
use Cmp\Storage\Strategy\AbstractStorageCallStrategy;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Prophet;
use Psr\Log\LoggerInterface;

class StorageBuilderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Cmp\Storage\StorageBuilder');
    }

    public function it_allows_setting_different_call_strategies(AbstractStorageCallStrategy $a)
    {
        $this->setStrategy($a)->shouldBe($this);
        $this->getStrategy()->shouldBe($a);
    }


    public function it_allows_setting_a_logger(LoggerInterface $l)
    {
        $this->setLogger($l)->shouldBe($this);
        $this->getLogger()->shouldBe($l);
    }


    public function it_has_a_default_logger()
    {
        $this->getLogger()->shouldHaveType('Cmp\Storage\Log\DefaultLogger');
    }

    public function it_allows_add_builtin_adapters()
    {
        $this->addAdapter('FileSystem')->shouldBe($this);
    }


    public function it_allows_add_already_initialized_adapter(AdapterInterface $vi)
    {
        $this->addAdapter($vi)->shouldBe($this);
    }


    public function it_throw_and_exception_when_the_adapter_is_not_valid()
    {
        $this->shouldThrow('\Cmp\Storage\Exception\StorageAdapterNotFoundException')->during('addAdapter', ['string']);
        $s = new \stdClass();
        $this->shouldThrow('\Cmp\Storage\Exception\InvalidStorageAdapterException')->during('addAdapter', [$s, []]);
        $this->shouldThrow('\Cmp\Storage\Exception\InvalidStorageAdapterException')->during('addAdapter', [$s]);
    }

    public function it_injects_logger_if_is_possible(LoggerInterface $loggerInterface)
    {
        $prophet = new Prophet();

        $adapterWithLogger = $prophet->prophesize();
        $adapterWithLogger->willImplement('\Psr\Log\LoggerAwareInterface');
        $adapterWithLogger->willImplement('\Cmp\Storage\AdapterInterface');


        $this->setLogger($loggerInterface);
        $this->addAdapter($adapterWithLogger);

        $adapterWithLogger->setLogger(Argument::any())->shouldHaveBeenCalled();
    }

    public function it_loads_the_the_default_if_no_other_has_been_been_added(AbstractStorageCallStrategy $callStrategy)
    {
        $this->build($callStrategy);
        $callStrategy->setAdapters(Argument::any())->shouldHaveBeenCalled();
    }

    public function it_returns_a_default_call_strategy_when_no_other_has_been_added(AdapterInterface $a)
    {
        $this->addAdapter($a);
        $storage = $this->build();
        $storage->getStrategyName()->shouldBe('CallAllStrategy');
    }


    public function it_builds_a_virtual_storage(AdapterInterface $vi, AbstractStorageCallStrategy $callStrategy)
    {
        $this->addAdapter($vi);
        $this->build($callStrategy)->shouldHaveType('\Cmp\Storage\VirtualStorageInterface');
        $callStrategy->setAdapters([$vi])->shouldHaveBeenCalled();
    }

    public function it_allows_specify_different_call_strategies(
        AdapterInterface $a,
        AbstractStorageCallStrategy $callStrategy
    ) {
        $this->addAdapter($a);
        $strategyName = "Dummy strategy";
        $callStrategy->setLogger(Argument::type('Psr\Log\LoggerInterface'))->shouldBeCalled();
        $callStrategy->getStrategyName()->willReturn($strategyName);
        $callStrategy->setAdapters([$a])->shouldBeCalled();
        $storage = $this->build($callStrategy);
        $storage->getStrategyName()->shouldBe($strategyName);
    }

    public function it_builds_a_virtual_storage_with_specific_call_strategy_and_logger_provider(
        AdapterInterface $a,
        AbstractStorageCallStrategy $callStrategy,
        LoggerInterface $loggerInterface
    ) {
        $this->addAdapter($a);
        $this->build($callStrategy, $loggerInterface)->shouldHaveType('\Cmp\Storage\VirtualStorageInterface');
    }
}

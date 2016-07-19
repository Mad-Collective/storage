<?php

namespace spec\Cmp\Storage\Strategy;

use Cmp\Storage\AdapterInterface;
use Cmp\Storage\Exception\FileNotFoundException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class CallAllStrategySpec extends ObjectBehavior
{
    function let(AdapterInterface $adapter1, AdapterInterface $adapter2, AdapterInterface $adapter3)
    {
        $this->addAdapters([$adapter1, $adapter2, $adapter3]);
        $this->shouldHaveType('Cmp\Storage\Strategy\CallAllStrategy');
    }

    function it_always_calls_all_their_adapters(
        AdapterInterface $adapter1,
        AdapterInterface $adapter2,
        AdapterInterface $adapter3
    ) {
        $path = "a/b/c";
        $adapter1->exists($path)->willReturn(true);
        $adapter2->exists($path)->willReturn(true);
        $adapter3->exists($path)->willReturn(true);
        $this->exists($path)->shouldBe(true);
    }


    function it_returns_the_most_optimistic_result(
        AdapterInterface $adapter1,
        AdapterInterface $adapter2,
        AdapterInterface $adapter3
    ) {
        $path = "a/b/c";
        $adapter1->exists($path)->willReturn(false);
        $adapter2->exists($path)->willReturn(false);
        $adapter3->exists($path)->willReturn(true);
        $this->exists($path)->shouldBe(true);
    }

    function it_logs_any_problem_with_the_adapters(
        LoggerInterface $logger,
        AdapterInterface $adapter1,
        AdapterInterface $adapter2,
        AdapterInterface $adapter3
    ) {

        $path = "a/b/c";
        $this->setLogger($logger);

        $adapter2->getName()->willReturn("ADAPTER DUMMY");
        $adapter1->delete($path)->willReturn(true);
        $adapter2->delete($path)->willThrow(new FileNotFoundException());
        $adapter3->delete($path)->willReturn(true);


        $this->delete($path)->shouldBe(true);

        $logger->log(
            LOG_ERR,
            'Adapter "ADAPTER DUMMY" fails on delete call.',
            Argument::any()
        )->shouldHaveBeenCalled();
    }

    function it_fails_if_all_the_adapters_fail(
        AdapterInterface $adapter1,
        AdapterInterface $adapter2,
        AdapterInterface $adapter3
    ) {
        $path = "a/b/c";
        $adapter1->exists($path)->willReturn(false);
        $adapter2->exists($path)->willReturn(false);
        $adapter3->exists($path)->willReturn(false);
        $this->exists($path)->shouldBe(false);
    }
}

<?php

namespace spec\Cmp\Storage\Strategy;

use Cmp\Storage\AdapterInterface;
use Cmp\Storage\Exception\FileNotFoundException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class FallBackChainStrategySpec extends ObjectBehavior
{
    function let(AdapterInterface $adapter1, AdapterInterface $adapter2, AdapterInterface $adapter3)
    {

        $this->addAdapters([$adapter1, $adapter2, $adapter3]);
        $this->shouldHaveType('Cmp\Storage\Strategy\FallBackChainStrategy');

    }


    function it_always_calls_the_first_adapter(
        AdapterInterface $adapter1,
        AdapterInterface $adapter2,
        AdapterInterface $adapter3
    ) {
        $path = "a/b/c";
        $adapter1->getName()->willReturn("A1");
        $adapter2->getName()->willReturn("A2");
        $adapter3->getName()->willReturn("A3");


        $adapter1->exists($path)->willReturn(true);
        $this->exists($path)->shouldBe(true);
        $adapter2->exists($path)->shouldNotHaveBeenCalled();
        $adapter3->exists($path)->shouldNotHaveBeenCalled();
    }


    function it_only_calls_the_following_adapter_if_the_previous_fails(
        AdapterInterface $adapter1,
        AdapterInterface $adapter2,
        AdapterInterface $adapter3
    ) {
        $path = "a/b/c";
        $adapter1->getName()->willReturn("ADAPTER DUMMY");
        $adapter1->get($path)->willThrow(new FileNotFoundException());

        $adapter2->get($path)->willReturn("hi!");
        $this->get($path)->shouldBe("hi!");
        $adapter3->exists($path)->shouldNotHaveBeenCalled();
    }

    function it_logs_any_problem_with_the_adapters(
        LoggerInterface $logger,
        AdapterInterface $adapter1,
        AdapterInterface $adapter2
    ) {
        $path = "a/b/c";
        $this->setLogger($logger);


        $adapter1->delete($path)->willThrow(new FileNotFoundException());
        $adapter1->getName()->willReturn("ADAPTER DUMMY");


        $adapter2->delete($path)->willReturn(false);
        $logger->log(LOG_ERR,
            'The adapter "ADAPTER DUMMY" is failing on delete call',
            ['exception' =>  Argument::any()]
        );
        $this->delete($path)->shouldBe(false);
    }


    function it_fails_if_all_adapters_fails(
        AdapterInterface $adapter1,
        AdapterInterface $adapter2,
        AdapterInterface $adapter3
    ) {
        $path = "a/b/c";
        $adapter1->get($path)->willThrow(new FileNotFoundException());
        $adapter1->getName()->willReturn("ADAPTER DUMMY 1");

        $adapter2->get($path)->willThrow(new FileNotFoundException());
        $adapter2->getName()->willReturn("ADAPTER DUMMY 2");

        $adapter3->get($path)->willThrow(new FileNotFoundException());
        $adapter3->getName()->willReturn("ADAPTER DUMMY 3");

        $this->shouldThrow('Cmp\Storage\Exception\FileNotFoundException')->during('get', [$path]);
    }

}

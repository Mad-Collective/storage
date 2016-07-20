<?php

namespace spec\Cmp\Storage;

use Cmp\Storage\Strategy\AbstractStorageCallStrategy;
use PhpSpec\ObjectBehavior;

class VirtualStorageSpec extends ObjectBehavior
{
    function let(AbstractStorageCallStrategy $callStrategy)
    {
        $this->beConstructedWith($callStrategy);
        $this->shouldHaveType('Cmp\Storage\VirtualStorage');
    }


    function it_run_calls_over_the_strategy(AbstractStorageCallStrategy $callStrategy)
    {
        $path = "/b/c";
        $newpath = "/b/d";
        $contents = "hi!";
        $stream = "stream";

        $callStrategy->exists($path)->willReturn(true);
        $this->exists($path)->shouldBe(true);

        $callStrategy->get($path)->willReturn($contents);
        $this->get($path)->shouldBe($contents);

        $callStrategy->getStream($path)->willReturn($contents);
        $this->getStream($path)->shouldBe($contents);

        $callStrategy->rename($path, $newpath)->willReturn(true);
        $this->rename($path, $newpath)->shouldBe(true);

        $callStrategy->delete($path)->willReturn(true);
        $this->delete($path)->shouldBe(true);

        $callStrategy->put($path, $contents)->willReturn(true);
        $this->put($path, $contents)->shouldBe(true);

        $callStrategy->putStream($path, $stream)->willReturn(true);
        $this->putStream($path, $stream)->shouldBe(true);
    }

    function it_knows_the_call_strategy(AbstractStorageCallStrategy $callStrategy)
    {

        $strategyName = "Dummy strategy";
        $callStrategy->getStrategyName()->willReturn($strategyName);
        $this->getCallStrategyName()->shouldBe($strategyName);
    }
}

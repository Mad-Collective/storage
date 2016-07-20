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
            'Adapter "ADAPTER DUMMY" fails.',
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

    public function it_wraps_the_rename_call(
        AdapterInterface $adapter1,
        AdapterInterface $adapter2,
        AdapterInterface $adapter3
    ) {

        $path = "/b/c";
        $newpath = "/b/d";
        $adapter1->rename($path, $newpath)->willReturn(true);
        $adapter2->rename($path, $newpath)->willReturn(true);
        $adapter3->rename($path, $newpath)->willReturn(true);
        $this->rename($path, $newpath)->shouldBe(true);


    }

    public function it_wraps_the_delete_call(
        AdapterInterface $adapter1,
        AdapterInterface $adapter2,
        AdapterInterface $adapter3
    ) {

        $path = "/b/c";
        $adapter1->delete($path)->willReturn(true);
        $adapter2->delete($path)->willReturn(true);
        $adapter3->delete($path)->willReturn(true);
        $this->delete($path)->shouldBe(true);


    }

    public function it_wraps_the_exists_call(
        AdapterInterface $adapter1,
        AdapterInterface $adapter2,
        AdapterInterface $adapter3
    ) {
        $path = "/b/c";
        $adapter1->exists($path)->willReturn(true);
        $adapter2->exists($path)->willReturn(true);
        $adapter3->exists($path)->willReturn(true);
        $this->exists($path)->shouldBe(true);

    }

    public function it_wraps_the_get_and_getStream_calls(
        AdapterInterface $adapter1,
        AdapterInterface $adapter2,
        AdapterInterface $adapter3
    ) {

        $path = "/b/c";
        $contents = "hi!";
        $adapter1->get($path)->willReturn($contents);
        $this->get($path)->shouldBe($contents);
        $adapter2->get($path)->shouldNotHaveBeenCalled();
        $adapter3->get($path)->shouldNotHaveBeenCalled();

        $adapter1->getStream($path)->willReturn($contents);
        $this->get($path)->shouldBe($contents);
        $adapter2->getStream($path)->shouldNotHaveBeenCalled();
        $adapter3->getStream($path)->shouldNotHaveBeenCalled();
    }


    public function it_wraps_the_put_and_putStream_calls(
        AdapterInterface $adapter1,
        AdapterInterface $adapter2,
        AdapterInterface $adapter3
    ) {

        $path = "/b/c";
        $contents = "hi!";
        $stream = "stream";
        $adapter1->put($path, $contents)->willReturn(true);
        $adapter2->put($path, $contents)->willReturn(true);
        $adapter3->put($path, $contents)->willReturn(true);
        $this->put($path, $contents)->shouldBe(true);

        $adapter1->putStream($path, $stream)->willReturn(true);
        $adapter2->putStream($path, $stream)->willReturn(true);
        $adapter3->putStream($path, $stream)->willReturn(true);
        $this->putStream($path, $stream)->shouldBe(true);
    }

}

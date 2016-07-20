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
        $logger->log(
            LOG_ERR,
            'The adapter "ADAPTER DUMMY" is failing.',
            ['exception' => Argument::any()]
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


    public function it_wraps_the_rename_call(
        AdapterInterface $adapter1,
        AdapterInterface $adapter2,
        AdapterInterface $adapter3
    ) {

        $path = "/b/c";
        $newpath = "/b/d";
        $adapter1->rename($path, $newpath)->willReturn(true);
        $this->rename($path, $newpath)->shouldBe(true);
        $adapter2->rename($path)->shouldNotHaveBeenCalled();
        $adapter3->rename($path)->shouldNotHaveBeenCalled();

    }

    public function it_wraps_the_delete_call(
        AdapterInterface $adapter1,
        AdapterInterface $adapter2,
        AdapterInterface $adapter3
    ) {

        $path = "/b/c";
        $adapter1->delete($path)->willReturn(true);
        $this->delete($path)->shouldBe(true);
        $adapter2->delete($path)->shouldNotHaveBeenCalled();
        $adapter3->delete($path)->shouldNotHaveBeenCalled();


    }

    public function it_wraps_the_exists_call(
        AdapterInterface $adapter1,
        AdapterInterface $adapter2,
        AdapterInterface $adapter3
    ) {
        $path = "/b/c";
        $adapter1->exists($path)->willReturn(true);
        $this->exists($path)->shouldBe(true);
        $adapter2->exists($path)->shouldNotHaveBeenCalled();
        $adapter3->exists($path)->shouldNotHaveBeenCalled();

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
        $this->put($path, $contents)->shouldBe(true);
        $adapter2->put($path)->shouldNotHaveBeenCalled();
        $adapter3->put($path)->shouldNotHaveBeenCalled();

        $adapter1->putStream($path, $stream)->willReturn(true);
        $this->putStream($path, $stream)->shouldBe(true);
        $adapter2->putStream($path)->shouldNotHaveBeenCalled();
        $adapter3->putStream($path)->shouldNotHaveBeenCalled();
    }
}

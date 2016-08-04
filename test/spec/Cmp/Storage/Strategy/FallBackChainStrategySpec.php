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
        $adapter1->getName()->willReturn("ADAPTER DUMMY");
        $adapter2->getName()->willReturn("ADAPTER DUMMY");
        $adapter3->getName()->willReturn("ADAPTER DUMMY");
        $this->setAdapters([$adapter1, $adapter2, $adapter3]);
        $this->shouldHaveType('Cmp\Storage\Strategy\FallBackChainStrategy');
    }

    function it_returns_the_first_non_failed_check_for_an_existing_file(
        AdapterInterface $adapter1,
        AdapterInterface $adapter2,
        AdapterInterface $adapter3
    ) {
        $path = "a/b/c";
        $adapter1->exists($path)->willReturn(false);
        $adapter2->exists($path)->willReturn(true);

        $this->exists($path)->shouldBe(true);

        $adapter3->exists($path)->shouldNotHaveBeenCalled();
    }

    function it_fails_with_a_false_if_no_adapter_cannot_has_the_file_available(
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

    function it_only_calls_the_following_adapter_if_the_previous_fails(
        AdapterInterface $adapter1,
        AdapterInterface $adapter2,
        AdapterInterface $adapter3
    ) {
        $path = "a/b/c";
        $adapter1->getName()->willReturn("ADAPTER DUMMY");
        $adapter1->get($path)->willThrow(new FileNotFoundException($path));

        $adapter2->get($path)->willReturn("hi!");

        $this->get($path)->shouldBe("hi!");

        $adapter3->get($path)->shouldNotHaveBeenCalled();
    }

    function it_fails_with_false_if_all_the_adapters_fail_but_at_least_one_tried(
        AdapterInterface $adapter1,
        AdapterInterface $adapter2,
        AdapterInterface $adapter3
    ) {
        $path = "a/b/c";
        $adapter1->get($path)->willThrow(new \LogicException());
        $adapter2->get($path)->willReturn(false);
        $adapter3->get($path)->willThrow(new \RuntimeException());

        $this->get($path)->shouldReturn(false);
    }

    function it_fails_with_the_first_exception_if_all_the_adapters_found_errors(
        AdapterInterface $adapter1,
        AdapterInterface $adapter2,
        AdapterInterface $adapter3
    ) {
        $path = "a/b/c";
        $firstException = new \LogicException();

        $adapter1->get($path)->willThrow($firstException);
        $adapter2->get($path)->willThrow(new \OutOfRangeException());
        $adapter3->get($path)->willThrow(new \RuntimeException());

        $this->shouldThrow($firstException)->duringGet($path);
    }

    function it_logs_any_problem_with_the_adapters(
        LoggerInterface $logger,
        AdapterInterface $adapter1,
        AdapterInterface $adapter2
    ) {
        $path = "a/b/c";
        $this->setLogger($logger);


        $adapter1->get($path)->willThrow(new FileNotFoundException($path));
        $adapter1->getName()->willReturn("ADAPTER DUMMY");

        $adapter2->get($path)->willReturn("test content");
        $logger->log(
            LOG_ERR,
            'The adapter "ADAPTER DUMMY" is failing.',
            ['exception' => Argument::any()]
        );
        $this->get($path)->shouldBe("test content");
    }


    function it_fails_if_all_adapters_fails(
        AdapterInterface $adapter1,
        AdapterInterface $adapter2,
        AdapterInterface $adapter3
    ) {
        $path = "a/b/c";
        $adapter1->get($path)->willThrow(new FileNotFoundException($path));
        $adapter1->getName()->willReturn("ADAPTER DUMMY 1");

        $adapter2->get($path)->willThrow(new FileNotFoundException($path));
        $adapter2->getName()->willReturn("ADAPTER DUMMY 2");

        $adapter3->get($path)->willThrow(new FileNotFoundException($path));
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

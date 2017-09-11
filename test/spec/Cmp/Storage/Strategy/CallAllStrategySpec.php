<?php

namespace spec\Cmp\Storage\Strategy;

use Cmp\Storage\Adapter\FileSystemAdapter;
use Cmp\Storage\AdapterInterface;
use Cmp\Storage\Exception\FileNotFoundException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class CallAllStrategySpec extends ObjectBehavior
{
    public function let(AdapterInterface $adapter1, AdapterInterface $adapter2, AdapterInterface $adapter3)
    {
        $this->setAdapters([$adapter1, $adapter2, $adapter3]);
        $this->shouldHaveType('Cmp\Storage\Strategy\CallAllStrategy');
    }

    public function it_always_calls_all_their_adapters(
        AdapterInterface $adapter1,
        AdapterInterface $adapter2,
        AdapterInterface $adapter3
    ) {
        $path = 'a/b/c';
        $adapter1->exists($path)->willReturn(true);
        $adapter2->exists($path)->willReturn(true);
        $adapter3->exists($path)->willReturn(true);
        $this->exists($path)->shouldBe(true);
    }

    public function it_returns_the_most_optimistic_result(
        AdapterInterface $adapter1,
        AdapterInterface $adapter2,
        AdapterInterface $adapter3
    ) {
        $path = 'a/b/c';
        $adapter1->exists($path)->willReturn(false);
        $adapter2->exists($path)->willReturn(false);
        $adapter3->exists($path)->willReturn(true);
        $this->exists($path)->shouldBe(true);
    }

    public function it_logs_any_problem_with_the_adapters(
        LoggerInterface $logger,
        AdapterInterface $adapter1,
        AdapterInterface $adapter2,
        AdapterInterface $adapter3
    ) {
        $path = 'a/b/c';
        $this->setLogger($logger);

        $adapter2->getName()->willReturn('ADAPTER DUMMY');
        $adapter1->delete($path)->willReturn(true);
        $adapter2->delete($path)->willThrow(new FileNotFoundException($path));
        $adapter3->delete($path)->willReturn(true);

        $this->delete($path)->shouldBe(true);

        $logger->log(
            LogLevel::ERROR,
            'Adapter "ADAPTER DUMMY" fails.',
            Argument::any()
        )->shouldHaveBeenCalled();
    }

    public function it_fails_if_all_the_adapters_fail(
        AdapterInterface $adapter1,
        AdapterInterface $adapter2,
        AdapterInterface $adapter3
    ) {
        $path = 'a/b/c';
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
        $path    = '/b/c';
        $newpath = '/b/d';
        $adapter1->rename($path, $newpath, false)->willReturn(true);
        $adapter2->rename($path, $newpath, false)->willReturn(true);
        $adapter3->rename($path, $newpath, false)->willReturn(true);
        $this->rename($path, $newpath)->shouldBe(true);

        $adapter1->rename($path, $newpath, true)->willReturn(true);
        $adapter2->rename($path, $newpath, true)->willReturn(true);
        $adapter3->rename($path, $newpath, true)->willReturn(true);
        $this->rename($path, $newpath, true)->shouldBe(true);
    }

    public function it_wraps_the_copy_call(
        AdapterInterface $adapter1,
        AdapterInterface $adapter2,
        AdapterInterface $adapter3
    ) {
        $path    = '/b/c';
        $newpath = '/b/d';
        $adapter1->copy($path, $newpath)->willReturn(true);
        $adapter2->copy($path, $newpath)->willReturn(true);
        $adapter3->copy($path, $newpath)->willReturn(true);
        $this->copy($path, $newpath)->shouldBe(true);
    }

    public function it_wraps_the_delete_call(
        AdapterInterface $adapter1,
        AdapterInterface $adapter2,
        AdapterInterface $adapter3
    ) {
        $path = '/b/c';
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
        $path = '/b/c';
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
        $path     = '/b/c';
        $contents = 'hi!';
        $adapter1->get($path)->willReturn($contents);
        $this->get($path)->shouldBe($contents);
        $adapter2->get($path)->shouldNotHaveBeenCalled();
        $adapter3->get($path)->shouldNotHaveBeenCalled();

        $adapter1->getStream($path)->willReturn($contents);
        $this->get($path)->shouldBe($contents);
        $adapter2->getStream($path)->shouldNotHaveBeenCalled();
        $adapter3->getStream($path)->shouldNotHaveBeenCalled();
    }

    public function it_throws_an_exception_if_a_file_cannot_be_found_on_any_adapter(
        AdapterInterface $adapter1,
        AdapterInterface $adapter2,
        AdapterInterface $adapter3
    ) {
        $path = '/b/c';
        $adapter1->get($path)->willReturn(false);
        $adapter2->get($path)->willReturn(false);
        $adapter3->get($path)->willReturn(false);

        $this->shouldThrow(new FileNotFoundException($path))->duringGet($path);
    }

    public function it_wraps_the_put_and_putStream_calls(
        AdapterInterface $adapter1,
        AdapterInterface $adapter2,
        AdapterInterface $adapter3
    ) {
        $path     = '/b/c';
        $contents = 'hi!';
        $stream   = 'stream';
        $adapter1->put($path, $contents)->willReturn(true);
        $adapter2->put($path, $contents)->willReturn(true);
        $adapter3->put($path, $contents)->willReturn(true);
        $this->put($path, $contents)->shouldBe(true);

        $adapter1->putStream($path, $stream)->willReturn(true);
        $adapter2->putStream($path, $stream)->willReturn(true);
        $adapter3->putStream($path, $stream)->willReturn(true);
        $this->putStream($path, $stream)->shouldBe(true);
    }

    public function it_log_on_action_error(FileSystemAdapter $dummyAdapter, LoggerInterface $logger)
    {

        $dummyAdapter->getName()->willReturn("Dummy adapter");
        $this->setLogger($logger);
        $dummyAdapter->setLogger($logger);
        $logger->log(LogLevel::INFO,'Add adapter "{adapter}" to strategy "{strategy}".',["adapter" => "Dummy adapter", "strategy" => "CallAllStrategy"])->shouldBeCalled();
        $this->addAdapter($dummyAdapter);

        $path     = '/b/c';
        $contents = 'hi!';
        $dummyAdapter->put($path, $contents)->willReturn(false);
        $logger->log(LogLevel::ERROR,"Impossible put file {file}.",["file" => "/b/c"])->shouldBeCalled();
        $this->put($path, $contents)->shouldBe(false);

    }
}

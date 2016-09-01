<?php

namespace spec\Cmp\Storage;

use Cmp\Storage\VirtualStorageInterface;
use PhpSpec\ObjectBehavior;

class MountPointSpec extends ObjectBehavior
{
    public function let(VirtualStorageInterface $virtualStorage)
    {
        $this->beConstructedWith('/a', $virtualStorage);
    }

    public function it_returns_the_storage(VirtualStorageInterface $virtualStorage)
    {
        $this->getStorage()->shouldBe($virtualStorage);
    }

    public function it_returns_virtual_the_path()
    {
        $this->getVirtualPath()->getPath()->shouldBe('/a');
    }
}

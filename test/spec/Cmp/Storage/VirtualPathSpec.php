<?php

namespace spec\Cmp\Storage;

use Cmp\Storage\Exception\RelativePathNotAllowed;
use Cmp\Storage\VirtualPath;
use PhpSpec\ObjectBehavior;

class VirtualPathSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $path = '/temp/a';
        $this->beConstructedWith($path);
        $this->shouldHaveType('Cmp\Storage\VirtualPath');
    }

    public function it_returns_the_path()
    {
        $path = '/temp/a';
        $this->beConstructedWith($path);
        $this->getPath()->shouldBe($path);
    }

    public function it_normalize_the_path()
    {
        $path = '/temp\\a/b/c';
        $this->beConstructedWith($path);
        $this->getPath()->shouldBe('/temp/a/b/c');
    }

    public function it_canonicalize_the_path()
    {
        $path = '/temp/a/b/c/../../d';
        $this->beConstructedWith($path);
        $this->getPath()->shouldBe('/temp/a/d');
    }

    public function it_allows_a_root_path()
    {
        $this->beConstructedWith('/');
        $this->getPath()->shouldBe('/');
    }

    public function it_trims_the_unnecesary_separators()
    {
        $path = '/temp/a/b/d/';
        $this->beConstructedWith($path);
        $this->getPath()->shouldBe('/temp/a/b/d');
    }

    public function it_only_allows_absolute_path()
    {
        $relative = 'a/b/d/';
        $this->beConstructedWith($relative);
        $this->shouldThrow(new RelativePathNotAllowed($relative))->duringInstantiation();
    }

    public function it_returns_if_path_is_child(VirtualPath $v1, VirtualPath $v2, VirtualPath $v3)
    {
        $path = '/temp/a/b';
        $v1->getPath()->willReturn('/temp/a/c/d');
        $v2->getPath()->willReturn('/temp/a/b/c');
        $v3->getPath()->willReturn('/temp/a/b');
        $this->beConstructedWith($path);
        $this->isChild($v1)->shouldBe(false);
        $this->isChild($v2)->shouldBe(true);
        $this->isChild($v3)->shouldBe(false);
    }
}

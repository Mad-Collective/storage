<?php

namespace spec\Cmp\Storage;

use Cmp\Storage\MountPoint;
use Cmp\Storage\MountPointSortedSet;
use Cmp\Storage\VirtualPath;
use PhpSpec\ObjectBehavior;

/**
 * Class MountPointHeapSpec.
 *
 * @mixin MountPointSortedSet
 */
class MountPointSortedSetSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Cmp\Storage\MountPointSortedSet');
    }

    public function it_sorts_by_path_depth(
        MountPoint $v1,
        MountPoint $v2,
        MountPoint $v3,
        VirtualPath $p1,
        VirtualPath $p2,
        VirtualPath $p3
    ) {
        $p1->getPath()->willReturn('/a/b/c');
        $v1->getVirtualPath()->willReturn($p1);

        $p2->getPath()->willReturn('/');
        $v2->getVirtualPath()->willReturn($p2);

        $p3->getPath()->willReturn('/a/b');
        $v3->getVirtualPath()->willReturn($p3);

        $this->set($v1);
        $this->set($v2);
        $this->set($v3);

        $iterator = $this->getIterator();

        $key = $iterator->key();
        $key->shouldBe('/a/b/c');
        $iterator->next();

        $key = $iterator->key();
        $key->shouldBe('/a/b');

        $iterator->next();
        $key = $iterator->key();
        $key->shouldBe('/');
    }

    public function it_sorts_paths_with_same_deep(
        MountPoint $v1,
        MountPoint $v2,
        MountPoint $v3,
        VirtualPath $p1,
        VirtualPath $p2,
        VirtualPath $p3
    ) {
        $p1->getPath()->willReturn('/a');
        $v1->getVirtualPath()->willReturn($p1);

        $p2->getPath()->willReturn('/b');
        $v2->getVirtualPath()->willReturn($p2);

        $p3->getPath()->willReturn('/');
        $v3->getVirtualPath()->willReturn($p3);

        $this->set($v1);
        $this->set($v2);
        $this->set($v3);

        $iterator = $this->getIterator();

        $key = $iterator->key();
        $key->shouldBe('/b');
        $iterator->next();

        $key = $iterator->key();
        $key->shouldBe('/a');

        $iterator->next();
        $key = $iterator->key();
        $key->shouldBe('/');
    }

    public function it_allows_replace_the_mount_points(MountPoint $v1, MountPoint $v2, VirtualPath $p1)
    {
        $p1->getPath()->willReturn('/a/b');
        $v1->getVirtualPath()->willReturn($p1);

        $v2->getVirtualPath()->willReturn($p1);

        $this->set($v1);
        $iterator = $this->getIterator();
        $iterator->count()->shouldBe(1);
        $iterator->current()->shouldBe($v1);

        $this->set($v2);
        $iterator = $this->getIterator();
        $iterator->count()->shouldBe(1);
        $iterator->current()->shouldBe($v2);
    }

    public function it_can_get_the_mountpoint_attached_for_a_path(MountPoint $v1, VirtualPath $p1)
    {
        $p1->getPath()->willReturn('/a/b/c');
        $v1->getVirtualPath()->willReturn($p1);

        $this->set($v1);

        $this->get('/x')->shouldBe(false);
        $this->get('/a/b/c')->shouldBe($v1);
    }

    public function it_can_remove_the_mountpoint_attached_for_a_path(MountPoint $v1, VirtualPath $p1)
    {
        $p1->getPath()->willReturn('/a/b/c');
        $v1->getVirtualPath()->willReturn($p1);

        $this->set($v1);
        $this->get('/a/b/c')->shouldBe($v1);

        $this->remove('/x')->shouldBe(false);
        $this->remove('/a/b/c')->shouldBe(true);
        $this->get('/a/b/c')->shouldBe(false);
    }
}

<?php

namespace spec\Cmp\Storage;

use Cmp\Storage\MountableVirtualStorage;
use Cmp\Storage\MountPoint;
use Cmp\Storage\VirtualPath;
use Cmp\Storage\VirtualStorageInterface;
use PhpSpec\ObjectBehavior;

/**
 * Class MountableVirtualStorageSpec.
 *
 * @mixin MountableVirtualStorage
 */
class MountableVirtualStorageSpec extends ObjectBehavior
{
    public function let(VirtualStorageInterface $defaultVirtualStorage)
    {
        $this->beConstructedWith($defaultVirtualStorage);
    }

    public function it_implements_the_virtual_storage_interface()
    {
        $this->shouldHaveType('Cmp\Storage\VirtualStorageInterface');
    }

    public function it_allows_add_a_concrete_virtual_storage_for_a_mount_point(
        MountPoint $mountPoint,
        MountPoint $mountPoint2,
        VirtualPath $v1,
        VirtualPath $v2
    ) {
        $this->getMountPoints()->count()->shouldBe(1); //because the default
        $path = '/tmp/a';
        $path2 = '/tmp/b';
        $v1->getPath()->willReturn($path);
        $v2->getPath()->willReturn($path2);
        $mountPoint->getVirtualPath()->willReturn($v1);
        $mountPoint2->getVirtualPath()->willReturn($v2);

        $this->registerMountPoint($mountPoint);
        $this->getMountPoints()->count()->shouldBe(2);

        $this->registerMountPoint($mountPoint2);
        $this->getMountPoints()->count()->shouldBe(3);

        $iterator = $this->getMountPoints();
        $key = $iterator->key();
        $key->shouldBe('/tmp/b');
        $iterator->next();
        $key = $iterator->key();
        $key->shouldBe('/tmp/a');
        $iterator->next();
        $key = $iterator->key();
        $key->shouldBe('/');
    }

    public function it_returns_the_mountpoint_assigned_to_one_path(
        VirtualStorageInterface $virtualStorage,
        MountPoint $mountPoint
    ) {
        $path = '/tmp/a';
        $v1 = new VirtualPath($path);
        $mountPoint->getStorage()->willReturn($virtualStorage);
        $mountPoint->getVirtualPath()->willReturn($v1);

        $this->registerMountPoint($mountPoint, $virtualStorage);
        $this->getMountPointForPath('/tmp/a/b/c/d/f')->shouldBe($mountPoint);
    }

    public function it_always_returns_the_nearest_mount_point(
        VirtualStorageInterface $virtualStorage1,
        VirtualStorageInterface $virtualStorage2,
        VirtualStorageInterface $virtualStorage3,
        MountPoint $mountPoint1,
        MountPoint $mountPoint2,
        MountPoint $mountPoint3
    ) {
        $path1 = '/tmp/';
        $path2 = '/tmp/a/b/c/d';
        $path3 = '/tmp/a/b/c/d/e/f';

        $mountPoint1->getStorage()->willReturn($virtualStorage1);
        $v1 = new VirtualPath($path1);
        $mountPoint1->getVirtualPath()->willReturn($v1);

        $mountPoint2->getStorage()->willReturn($virtualStorage2);
        $v2 = new VirtualPath($path2);
        $mountPoint2->getVirtualPath()->willReturn($v2);

        $mountPoint3->getStorage()->willReturn($virtualStorage3);
        $v3 = new VirtualPath($path3);
        $mountPoint3->getVirtualPath()->willReturn($v3);

        $this->registerMountPoint($mountPoint1);
        $this->registerMountPoint($mountPoint2);
        $this->registerMountPoint($mountPoint3);
        $test1 = '/tmp/a';
        $test2 = '/tmp/a/b/c';
        $test3 = '/tmp/a/b/c/d/e/f/n';
        $this->getMountPointForPath($test1)->shouldBe($mountPoint1);
        $this->getMountPointForPath($test2)->shouldBe($mountPoint1);
        $this->getMountPointForPath($test3)->shouldBe($mountPoint3);
    }

    public function it_has_a_default_virtual_storage(VirtualStorageInterface $defaultVirtualStorage)
    {
        $path = '/tmp/a/b/d/e/f/g';
        $this->getMountPointForPath($path)->getStorage()->shouldBe($defaultVirtualStorage);
    }

    public function it_can_move_files_between_mount_points(
        VirtualStorageInterface $fsStorage,
        VirtualStorageInterface $awsStorage,
        MountPoint $mountPoint1,
        MountPoint $mountPoint2
    ) {
        $pathTemp = '/tmp/';
        $pathPublic = '/var/www/public';

        $fileSrc = '/tmp/upload.txt';
        $fileDst = '/var/www/public/assets/upload.txt';

        //stream creation
        $string = "Hi I'm a stream.";
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $string);
        rewind($stream);

        $fsStorage->exists($fileSrc)->shouldBeCalled()->willReturn(true);
        $awsStorage->exists($fileDst)->shouldBeCalled()->willReturn(false, true);
        $fsStorage->getStream($fileSrc)->shouldBeCalled()->willReturn($stream);
        $awsStorage->putStream($fileDst, $stream)->shouldBeCalled()->willReturn(true);
        $fsStorage->delete($fileSrc)->shouldBeCalled()->willReturn(true);

        $mountPoint1->getStorage()->willReturn($fsStorage);
        $v1 = new VirtualPath($pathTemp);
        $mountPoint1->getVirtualPath()->willReturn($v1);

        $mountPoint2->getStorage()->willReturn($awsStorage);
        $v2 = new VirtualPath($pathPublic);
        $mountPoint2->getVirtualPath()->willReturn($v2);

        $this->registerMountPoint($mountPoint1);
        $this->registerMountPoint($mountPoint2);

        $this->rename($fileSrc, $fileDst);
    }

    public function it_checks_if_some_file_exists_in_the_mount_point(
        VirtualStorageInterface $virtualStorage,
        MountPoint $mountPoint
    ) {
        $mount = '/tmp';
        $path = '/tmp/test';

        $mountPoint->getStorage()->willReturn($virtualStorage);
        $vp = new VirtualPath($mount);
        $mountPoint->getVirtualPath()->willReturn($vp);
        $this->registerMountPoint($mountPoint);

        $virtualStorage->exists($path)->shouldBeCalled()->willReturn(true);
        $this->exists($path)->shouldBe(true);
    }

    public function it_gets_a_file_available_in_the_mount_point(
        VirtualStorageInterface $virtualStorage,
        MountPoint $mountPoint
    ) {
        $mount = '/tmp';
        $path = '/tmp/test';
        $content = 'hi!';

        $mountPoint->getStorage()->willReturn($virtualStorage);
        $vp = new VirtualPath($mount);
        $mountPoint->getVirtualPath()->willReturn($vp);

        $this->registerMountPoint($mountPoint);
        $virtualStorage->get($path)->shouldBeCalled()->willReturn($content);
        $virtualStorage->getStream($path)->shouldBeCalled()->willReturn($content);
        $this->get($path)->shouldBe($content);
        $this->getStream($path)->shouldBe($content);
    }

    public function it_puts_a_file_in_the_mount_point(VirtualStorageInterface $virtualStorage, MountPoint $mountPoint)
    {
        $mount = '/tmp';
        $path = '/tmp/test';
        $content = 'hi!';

        $mountPoint->getStorage()->willReturn($virtualStorage);
        $vp = new VirtualPath($mount);
        $mountPoint->getVirtualPath()->willReturn($vp);
        $this->registerMountPoint($mountPoint);

        $virtualStorage->put($path, $content)->shouldBeCalled()->willReturn(true);
        $virtualStorage->putStream($path, $content)->shouldBeCalled()->willReturn(true);
        $this->put($path, $content)->shouldBe(true);
        $this->putStream($path, $content)->shouldBe(true);
    }

    public function it_deletes_a_file_in_the_mount_point(VirtualStorageInterface $virtualStorage, MountPoint $mountPoint)
    {
        $mount = '/tmp';
        $path = '/tmp/test';

        $mountPoint->getStorage()->willReturn($virtualStorage);
        $vp = new VirtualPath($mount);
        $mountPoint->getVirtualPath()->willReturn($vp);

        $this->registerMountPoint($mountPoint);
        $virtualStorage->delete($path)->shouldBeCalled()->willReturn(true);
        $this->delete($path)->shouldBe(true);
    }
}

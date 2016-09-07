<?php

class MountPointsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Cmp\Storage\Adapter\S3AWSAdapter
     */
    private $s3Adapter;
    private $fileSystemStorage;
    private $vfs;

    public function setUp()
    {
        $this->s3Adapter = new \Cmp\Storage\Adapter\S3AWSAdapter();
        $this->fileSystemStorage = new \Cmp\Storage\Adapter\FileSystemAdapter();

        $paths = $this->getAvailablePath();


        $localMountPoint = new \Cmp\Storage\MountPoint($paths['tmp'], $this->fileSystemStorage);
        $secretMountPoint = new \Cmp\Storage\MountPoint($paths['secret'], $this->fileSystemStorage);
        $publicMountPoint = new \Cmp\Storage\MountPoint($paths['public'], $this->s3Adapter);

        $this->vfs = new \Cmp\Storage\MountableVirtualStorage($this->fileSystemStorage);
        $this->vfs->registerMountPoint($localMountPoint);
        $this->vfs->registerMountPoint($secretMountPoint);
        $this->vfs->registerMountPoint($publicMountPoint);
    }

    /**
     * @dataProvider pathProvider
     */
    public function testFileExists($path)
    {
        $filename = $this->getTempFileNameInPath($path);
        $this->assertFalse($this->vfs->exists($filename));
        $this->assertTrue($this->vfs->put($filename, 'testFileExists'));
        $this->assertTrue($this->vfs->exists($filename));
    }

    /**
     * @dataProvider pathProvider
     */
    public function testFileGet($path)
    {
        $filename = $this->getTempFileNameInPath($path);
        $content = 'This is a get test: '.rand(0, 1000);
        $this->assertTrue($this->vfs->put($filename, $content));
        $this->assertEquals($content, $this->vfs->get($filename));
    }

    /**
     * @dataProvider pathProvider
     */
    public function testFileGetStream($path)
    {
        $filename = $this->getTempFileNameInPath($path);
        $content = 'This is a get test: '.rand(0, 1000)."\n";
        $this->assertTrue($this->vfs->put($filename, $content));

        $stream = $this->vfs->getStream($filename);
        $buffer = fgets($stream, 4096);
        fclose($stream);
        $this->assertEquals($content, $buffer);
    }

    /**
     * @dataProvider pathProvider
     */
    public function testFileRename($path)
    {
        $filenameOld = $this->getTempFileNameInPath($path);
        $filenameNew = $this->getTempFileNameInPath($path);
        $this->assertFalse($this->vfs->exists($filenameOld));
        $this->assertFalse($this->vfs->exists($filenameNew));
        $this->assertTrue($this->vfs->put($filenameOld, 'testFileRename'));
        $this->assertFalse($this->vfs->exists($filenameNew));
        $this->assertTrue($this->vfs->rename($filenameOld, $filenameNew));
        $this->assertFalse($this->vfs->exists($filenameOld));
        $this->assertTrue($this->vfs->exists($filenameNew));
    }

    /**
     * @dataProvider pathProvider
     */
    public function testFileDelete($path)
    {
        $filename = $this->getTempFileNameInPath($path);
        $this->assertFalse($this->vfs->exists($filename));
        $this->assertTrue($this->vfs->put($filename, 'testFileDelete'));
        $this->assertTrue($this->vfs->exists($filename));
        $this->assertTrue($this->vfs->delete($filename));
        $this->assertFalse($this->vfs->exists($filename));
    }

    /**
     * @dataProvider pathProvider
     */
    public function testFilePut($path)
    {
        $content = 'This is a put test: '.rand(0, 1000)."\n";
        $filename = $this->getTempFileNameInPath($path);
        $this->assertFalse($this->vfs->exists($filename));
        $this->assertTrue($this->vfs->put($filename, $content));
        $this->assertTrue($this->vfs->exists($filename));
        $this->assertEquals($content, $this->vfs->get($filename));
    }

    /**
     * @dataProvider pathProvider
     */
    public function testFilePutStream($path)
    {
        $content = 'This is a putstrem test: '.rand(0, 1000)."\n";
        $resource = fopen('php://memory', 'r+');
        fwrite($resource, $content);
        rewind($resource);

        $filename = $this->getTempFileNameInPath($path);
        $this->assertFalse($this->vfs->exists($filename));
        $this->assertTrue($this->vfs->putStream($filename, $resource));
        $this->assertTrue($this->vfs->exists($filename));
        $this->assertEquals($content, $this->vfs->get($filename));
    }

    public function moveFilesBetweenEndpoints()
    {
        $path = $this->getAvailablePath();
        $filenameOld = $this->getTempFileNameInPath($path['tmp']);
        $filenameNew = $this->getTempFileNameInPath($path['public']);

        $this->assertFalse($this->vfs->exists($filenameOld));
        $this->assertFalse($this->vfs->exists($filenameNew));

        $this->assertTrue($this->vfs->put($filenameOld, 'testFileRename'));
        $this->assertFalse($this->vfs->exists($filenameNew));

        $this->assertTrue($this->vfs->rename($filenameOld, $filenameNew));
        $this->assertFalse($this->vfs->exists($filenameOld));
        $this->assertTrue($this->vfs->exists($filenameNew));
    }


    public function testFilePutWithStrategies()
    {
        $path =  sys_get_temp_dir().DIRECTORY_SEPARATOR.'strategy';
        $callAllStrategy = (new \Cmp\Storage\StorageBuilder())
            ->setStrategy(
                new \Cmp\Storage\Strategy\CallAllStrategy()
            )
            ->addAdapter($this->s3Adapter)
            ->addAdapter($this->fileSystemStorage)
            ->build();


        $this->vfs->registerMountPoint(new \Cmp\Storage\MountPoint($path,$callAllStrategy));

        $content = 'This is a put test: '.rand(0, 1000)."\n";
        $filename = $this->getTempFileNameInPath($path);
        $this->assertFalse($this->vfs->exists($filename));
        $this->assertTrue($this->vfs->put($filename, $content));
        $this->assertTrue($this->vfs->exists($filename));
        $this->assertEquals($content, $this->vfs->get($filename));
    }


    public function pathProvider()
    {
        $paths = $this->getAvailablePath();

        return [
            [$paths['rand']],
            [$paths['tmp']],
            [$paths['secret']],
            [$paths['public']],
        ];
    }

    private function getTempFileNameInPath($localMountPoint)
    {
        $path = '';
        while (true) {
            $filename = uniqid('TestMountPoint', true).'.test';
            $path = $localMountPoint.DIRECTORY_SEPARATOR.$filename;
            if (!file_exists($path)) {
                break;
            }
        }

        return $path;
    }


    private function getAvailablePath()
    {
        return [
            'rand' => sys_get_temp_dir().DIRECTORY_SEPARATOR.'random',
            'tmp' => sys_get_temp_dir().DIRECTORY_SEPARATOR.'local',
            'secret' => sys_get_temp_dir().DIRECTORY_SEPARATOR.'secret',
            'public' => DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'assets',
        ];
    }
}

<?php
use Cmp\Storage\Adapter\FileSystemAdapter;
use PHPUnit\Framework\TestCase;

class FileSystemAdapterTest extends TestCase
{

    /**
     * @var FileSystemAdapter
     */
    private $fileSystemStorage;

    public function setUp()
    {
        $this->fileSystemStorage = new \Cmp\Storage\Adapter\FileSystemAdapter();
    }


    public function testFileExists()
    {
        $filename = $this->getTempFileName();
        $this->assertFileNotExists($filename);
        $this->assertFalse($this->fileSystemStorage->exists($filename));
        $this->assertTrue($this->fileSystemStorage->put($filename, "testFileExists"));
        $this->assertFileExists($filename);
        $this->assertTrue($this->fileSystemStorage->exists($filename));
    }

    public function testFileGet()
    {
        $filename = $this->getTempFileName();
        $content = "This is a get test: ".rand(0, 1000);
        $this->assertTrue($this->fileSystemStorage->put($filename, $content));
        $this->assertFileExists($filename);
        $this->assertEquals($content, $this->fileSystemStorage->get($filename));
    }

    public function testFileGetStream()
    {
        $filename = $this->getTempFileName();
        $content = "This is a get test: ".rand(0, 1000)."\n";
        $this->assertTrue($this->fileSystemStorage->put($filename, $content));
        $this->assertFileExists($filename);

        $stream = $this->fileSystemStorage->getStream($filename);
        $buffer = fgets($stream, 4096);
        fclose($stream);
        $this->assertEquals($content, $buffer);
    }

    public function testFileRename()
    {
        $filenameOld = $this->getTempFileName();
        $filenameNew = $this->getTempFileName();
        $this->assertFileNotExists($filenameOld);
        $this->assertFileNotExists($filenameNew);
        $this->assertTrue($this->fileSystemStorage->put($filenameOld, "testFileRename"));
        $this->assertFileExists($filenameOld);
        $this->assertTrue($this->fileSystemStorage->rename($filenameOld, $filenameNew));
        $this->assertFileNotExists($filenameOld);
        $this->assertFileExists($filenameNew);
    }

    public function testFileDelete()
    {
        $filename = $this->getTempFileName();
        $this->assertFileNotExists($filename);
        $this->assertTrue($this->fileSystemStorage->put($filename, "testFileDelete"));
        $this->assertFileExists($filename);
        $this->assertTrue($this->fileSystemStorage->delete($filename));
        $this->assertFileNotExists($filename);
    }


    public function testFilePut()
    {
        $content = "This is a put test: ".rand(0, 1000)."\n";
        $filename = $this->getTempFileName();
        $this->assertFileNotExists($filename);
        $this->assertTrue($this->fileSystemStorage->put($filename, $content));
        $this->assertFileExists($filename);
        $this->assertEquals($content, file_get_contents($filename));
    }

    public function testFilePutStream()
    {
        $content = "This is a putstrem test: ".rand(0, 1000)."\n";
        $resource = fopen('php://memory', 'r+');
        fwrite($resource, $content);
        rewind($resource);

        $filename = $this->getTempFileName();
        $this->assertFileNotExists($filename);
        $this->assertTrue($this->fileSystemStorage->putStream($filename, $resource));
        $this->assertFileExists($filename);
        $this->assertEquals($content, file_get_contents($filename));
    }

    /**
     * @expectedException \Cmp\Storage\Exception\InvalidPathException
     */
    public function testBadPath()
    {
        $this->assertTrue($this->fileSystemStorage->put("/tmp", "fail"));
    }


    public function testParentPathCreation()
    {
        $filename = uniqid('TestAdapter', true).'.test';
        $path = sys_get_temp_dir().
                DIRECTORY_SEPARATOR.rand(1, 1000).
                DIRECTORY_SEPARATOR.rand(1, 1000).
                DIRECTORY_SEPARATOR.rand(1, 1000).
                DIRECTORY_SEPARATOR.$filename;

        $this->assertFileNotExists($path);
        $this->assertTrue($this->fileSystemStorage->put($path, "testParentPathCreation"));
        $this->assertFileExists($path);
        $this->assertTrue($this->fileSystemStorage->exists($path));
    }


    private function getTempFileName()
    {
        while (true) {
            $filename = uniqid('TestAdapter', true).'.test';
            $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.$filename;
            if (!file_exists($path)) {
                break;
            }
        }

        return $path;
    }
}

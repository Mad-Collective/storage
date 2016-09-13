<?php

use Cmp\Storage\Adapter\FileSystemAdapter;

class FileSystemAdapterTest extends PHPUnit_Framework_TestCase
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
        $this->assertTrue($this->fileSystemStorage->put($filename, 'testFileExists'));
        $this->assertFileExists($filename);
        $this->assertTrue($this->fileSystemStorage->exists($filename));
    }

    public function testFileGet()
    {
        $filename = $this->getTempFileName();
        $content = 'This is a get test: '.rand(0, 1000);
        $this->assertTrue($this->fileSystemStorage->put($filename, $content));
        $this->assertFileExists($filename);
        $this->assertEquals($content, $this->fileSystemStorage->get($filename));
    }

    public function testFileGetStream()
    {
        $filename = $this->getTempFileName();
        $content = 'This is a get test: '.rand(0, 1000)."\n";
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
        $this->assertTrue($this->fileSystemStorage->put($filenameOld, 'testFileRename'));
        $this->assertFileExists($filenameOld);
        $this->assertTrue($this->fileSystemStorage->rename($filenameOld, $filenameNew));
        $this->assertFileNotExists($filenameOld);
        $this->assertFileExists($filenameNew);
    }

    public function testFileCopy()
    {
        $filenameOld = $this->getTempFileName();
        $filenameNew = $this->getTempFileName();
        $this->assertFileNotExists($filenameOld);
        $this->assertFileNotExists($filenameNew);
        $this->assertTrue($this->fileSystemStorage->put($filenameOld, 'testFileRename'));
        $this->assertFileExists($filenameOld);
        $this->assertTrue($this->fileSystemStorage->copy($filenameOld, $filenameNew));
        $this->assertFileExists($filenameOld);
        $this->assertFileExists($filenameNew);
    }


    public function testFileRenameWithOverWrite()
    {
        $filenameOld = $this->getTempFileName();
        $filenameNew = $this->getTempFileName();
        $this->assertTrue($this->fileSystemStorage->put($filenameOld, 'testFileRenameWithOverWrite'));
        $this->assertTrue($this->fileSystemStorage->put($filenameNew, 'testFileRenameWithOverWrite'));

        try {
            $this->fileSystemStorage->rename($filenameOld, $filenameNew);
            $this->assertTrue(false);
        } catch (\Cmp\Storage\Exception\FileExistsException $e) {
            $this->assertTrue(true);
        }

        $this->assertTrue($this->fileSystemStorage->rename($filenameOld, $filenameNew, true));

        $this->assertFileNotExists($filenameOld);
        $this->assertFileExists($filenameNew);
    }

    public function testFileDelete()
    {
        $filename = $this->getTempFileName();
        $this->assertFileNotExists($filename);
        $this->assertTrue($this->fileSystemStorage->put($filename, 'testFileDelete'));
        $this->assertFileExists($filename);
        $this->assertTrue($this->fileSystemStorage->delete($filename));
        $this->assertFileNotExists($filename);
    }

    public function testDirectoryDelete()
    {
        $directoryPath = $this->getTempDirectoryPath();
        $filename = $directoryPath.DIRECTORY_SEPARATOR.'test.txt';
        $this->assertFileNotExists($filename);
        $this->assertTrue($this->fileSystemStorage->put($filename, 'testFileDelete'));
        $this->assertFileExists($filename);
        $this->assertTrue($this->fileSystemStorage->delete($directoryPath));
        $this->assertFileNotExists($filename);
    }

    public function testFilePut()
    {
        $content = 'This is a put test: '.rand(0, 1000)."\n";
        $filename = $this->getTempFileName();
        $this->assertFileNotExists($filename);
        $this->assertTrue($this->fileSystemStorage->put($filename, $content));
        $this->assertFileExists($filename);
        $this->assertEquals($content, file_get_contents($filename));
    }

    public function testFilePutStream()
    {
        $content = 'This is a putstrem test: '.rand(0, 1000)."\n";
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
        $this->assertTrue($this->fileSystemStorage->put('/tmp', 'fail'));
    }

    /**
     * @expectedException \Cmp\Storage\Exception\InvalidPathException
     */
    public function testFSPathLimit()
    {
        $fileName = $this->generateRandomString(270);
        $this->assertTrue($this->fileSystemStorage->put("/tmp/$fileName", 'fail'));
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
        $this->assertTrue($this->fileSystemStorage->put($path, 'testParentPathCreation'));
        $this->assertFileExists($path);
        $this->assertTrue($this->fileSystemStorage->exists($path));
    }

    private function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; ++$i) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    private function getTempFileName()
    {
        $path = '';
        while (true) {
            $filename = uniqid('TestAdapter', true).'.test';
            $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.$filename;
            if (!file_exists($path)) {
                break;
            }
        }

        return $path;
    }

    private function getTempDirectoryPath()
    {
        $path = '';
        while (true) {
            $filename = uniqid('TestAdapter', true);
            $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.$filename;
            if (!file_exists($path)) {
                break;
            }
        }

        return $path;
    }
}

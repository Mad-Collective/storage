<?php

class S3AwsAdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Cmp\Storage\Adapter\S3AWSAdapter
     */
    private $s3Adapter;

    public function setUp()
    {
        $this->s3Adapter = new \Cmp\Storage\Adapter\S3AWSAdapter();
    }

    public function testFileExists()
    {
        $filename = $this->getTempFileName();
        $this->assertFalse($this->s3Adapter->exists($filename));
        $this->assertTrue($this->s3Adapter->put($filename, 'testFileExists'));
        $this->assertTrue($this->s3Adapter->exists($filename));
    }

    public function testFileGetAndPut()
    {
        $filename = $this->getTempFileName();
        $content = 'This is a get test: '.rand(0, 1000);
        $this->assertTrue($this->s3Adapter->put($filename, $content));
        $this->assertTrue($this->s3Adapter->exists($filename));
        $this->assertEquals($content, $this->s3Adapter->get($filename));
    }

    public function testFileGetStream()
    {
        $filename = $this->getTempFileName();
        $content = 'This is a get test: '.rand(0, 1000)."\n";
        $this->assertTrue($this->s3Adapter->put($filename, $content));
        $this->assertTrue($this->s3Adapter->exists($filename));
        $stream = $this->s3Adapter->getStream($filename);
        $buffer = fgets($stream, 4096);
        fclose($stream);
        $this->assertEquals($content, $buffer);
    }

    public function testFileRename()
    {
        $filenameOld = $this->getTempFileName();
        $filenameNew = $this->getTempFileName();
        $this->assertFalse($this->s3Adapter->exists($filenameOld));
        $this->assertFalse($this->s3Adapter->exists($filenameNew));
        $this->assertTrue($this->s3Adapter->put($filenameOld, 'testFileRename'));
        $this->assertTrue($this->s3Adapter->exists($filenameOld));
        $this->assertTrue($this->s3Adapter->rename($filenameOld, $filenameNew));
        $this->assertFalse($this->s3Adapter->exists($filenameOld));
        $this->assertTrue($this->s3Adapter->exists($filenameNew));
    }

    public function testFileCopy()
    {
        $filenameOld = $this->getTempFileName();
        $filenameNew = $this->getTempFileName();
        $this->assertFalse($this->s3Adapter->exists($filenameOld));
        $this->assertFalse($this->s3Adapter->exists($filenameNew));
        $this->assertTrue($this->s3Adapter->put($filenameOld, 'testFileRename'));
        $this->assertTrue($this->s3Adapter->exists($filenameOld));
        $this->assertTrue($this->s3Adapter->copy($filenameOld, $filenameNew));
        $this->assertTrue($this->s3Adapter->exists($filenameOld));
        $this->assertTrue($this->s3Adapter->exists($filenameNew));
    }

    public function testFileRenameWithOverWrite()
    {
        $filenameOld = $this->getTempFileName();
        $filenameNew = $this->getTempFileName();
        $this->assertTrue($this->s3Adapter->put($filenameOld, 'testFileRenameWithOverWrite'));
        $this->assertTrue($this->s3Adapter->put($filenameNew, 'testFileRenameWithOverWrite'));

        try {
            $this->s3Adapter->rename($filenameOld, $filenameNew);
            $this->assertTrue(false);
        } catch (\Cmp\Storage\Exception\FileExistsException $e) {
            $this->assertTrue(true);
        }

        $this->assertTrue($this->s3Adapter->rename($filenameOld, $filenameNew, true));
        $this->assertFalse($this->s3Adapter->exists($filenameOld));
        $this->assertTrue($this->s3Adapter->exists($filenameNew));
    }

    public function testFileDelete()
    {
        $filename = $this->getTempFileName();
        $this->assertFalse($this->s3Adapter->exists($filename));
        $this->assertTrue($this->s3Adapter->put($filename, 'testFileDelete'));
        $this->assertTrue($this->s3Adapter->exists($filename));
        $this->assertTrue($this->s3Adapter->delete($filename));
        $this->assertFalse($this->s3Adapter->exists($filename));
    }

    public function testDirectoryDelete()
    {
        $dirPath = $this->getTempDirPath();
        $filename = $dirPath.DIRECTORY_SEPARATOR.'test.txt';
        $this->assertFalse($this->s3Adapter->exists($filename));
        $this->assertTrue($this->s3Adapter->put($filename, 'testFileDelete'));
        $this->assertTrue($this->s3Adapter->exists($filename));
        $this->assertTrue($this->s3Adapter->exists($dirPath));
        $this->assertTrue($this->s3Adapter->delete($dirPath));
        $this->assertFalse($this->s3Adapter->exists($filename));
        $this->assertFalse($this->s3Adapter->exists($dirPath));
    }

    public function testFilePutStream()
    {
        $content = 'This is a putstrem test: '.rand(0, 1000)."\n";
        $resource = fopen('php://memory', 'r+');
        fwrite($resource, $content);
        rewind($resource);

        $filename = $this->getTempFileName();
        $this->assertFalse($this->s3Adapter->exists($filename));
        $this->assertTrue($this->s3Adapter->putStream($filename, $resource));
        $this->assertTrue($this->s3Adapter->exists($filename));
        $this->assertEquals($content, $this->s3Adapter->get($filename));
    }

    public function testParentPathCreation()
    {
        $filename = uniqid('TestAdapter', true).'.test';
        $path = DIRECTORY_SEPARATOR.rand(1, 1000).
                DIRECTORY_SEPARATOR.rand(1, 1000).
                DIRECTORY_SEPARATOR.rand(1, 1000).
                DIRECTORY_SEPARATOR.$filename;

        $this->assertFalse($this->s3Adapter->exists($path));
        $this->assertTrue($this->s3Adapter->put($path, 'testParentPathCreation'));
        $this->assertTrue($this->s3Adapter->exists($path));
    }

    public function testPathWithPrefix()
    {
        $s3Adapter = new \Cmp\Storage\Adapter\S3AWSAdapter([], null, '/dir');
        $prefix = '/dir/folder/';
        $fileName = $this->getTempFileName();
        $this->assertFalse($s3Adapter->exists($prefix.$fileName));
        $this->assertFalse($s3Adapter->exists('/folder/'.$fileName));
        $this->assertTrue($s3Adapter->put($prefix.$fileName, 'test'));
        $this->assertTrue($s3Adapter->exists($prefix.$fileName));
        $this->assertTrue($s3Adapter->exists('/folder/'.$fileName));
    }

    private function getTempFileName()
    {
        return uniqid('s3', true).'.test';
    }

    private function getTempDirPath()
    {
        return uniqid('s3', true);
    }
}

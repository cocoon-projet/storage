<?php

use PHPUnit\Framework\TestCase;
use League\Flysystem\Filesystem;
use Cocoon\StorageManager\Finder;
use Cocoon\StorageManager\Storage;
use Cocoon\StorageManager\FileManager;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Cocoon\StorageManager\Exceptions\StorageOperationException;

class StorageTest extends TestCase
{
    protected $basePath = __DIR__ . '/tests/storage/';
    protected $filesystem;

    protected function setUp(): void
    {
        $adapter = new LocalFilesystemAdapter($this->basePath);
        $this->filesystem = new Filesystem($adapter);
        Storage::init($this->basePath);
    }

    public function testFile()
    {
        $fileManager = Storage::file('test.txt');
        $this->assertInstanceOf(FileManager::class, $fileManager);
    }

    public function testFind()
    {
        $finder = Storage::find();
        $this->assertInstanceOf(Finder::class, $finder);
    }

    public function testAdapter()
    {
        $adapter = new LocalFilesystemAdapter($this->basePath);
        Storage::adapter($adapter);
        $this->assertInstanceOf(LocalFilesystemAdapter::class, Storage::adapter());
    }

    public function testPut()
    {
        Storage::put('test.txt', 'Hello, World!');
        $this->assertTrue(Storage::exists('test.txt'));
    }

    public function testGet()
    {
        Storage::put('test.txt', 'Hello, World!');
        $content = Storage::get('test.txt');
        $this->assertEquals('Hello, World!', $content);
    }

    public function testDelete()
    {
        Storage::put('test.txt', 'Hello, World!');
        Storage::delete('test.txt');
        $this->assertFalse(Storage::exists('test.txt'));
    }

    public function testCopy()
    {
        Storage::put('test.txt', 'Hello, World!');
        Storage::copy('test.txt', 'copy_test.txt');
        $this->assertTrue(Storage::exists('copy_test.txt'));
    }

    public function testMove()
    {
        Storage::put('test.txt', 'Hello, World!');
        Storage::move('test.txt', 'moved_test.txt');
        $this->assertTrue(Storage::exists('moved_test.txt'));
        $this->assertFalse(Storage::exists('test.txt'));
    }

    public function testExists()
    {
        Storage::put('test.txt', 'Hello, World!');
        $this->assertTrue(Storage::exists('test.txt'));
    }

    public function testMkdir()
    {
        Storage::mkdir('/test_directory');
        $return = Storage::exists('/test_directory');
        $this->assertTrue($return);
    }

    public function testRmdir()
    {
        Storage::mkdir('/test_directory');
        Storage::rmdir('/test_directory');
        $this->assertFalse(Storage::exists('test_directory'));
    }
    public function testPutException()
    {

        $this->expectException(StorageOperationException::class);
        Storage::put('', 'ok');

    }

    public function testGetException()
    {

        $this->expectException(StorageOperationException::class);
        Storage::get('file_none.txt');
    }

    public function testDeleteException()
    {
        $this->expectException(StorageOperationException::class);
        Storage::delete('');
    }

    public function testMoveException()
    {
        $this->expectException(StorageOperationException::class);
        Storage::move('', 'ok');
    }

    public function testCopyException()
    {
        $this->expectException(StorageOperationException::class);
        Storage::copy('', 'ok');
    }  
}

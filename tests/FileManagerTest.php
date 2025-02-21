<?php

use PHPUnit\Framework\TestCase;
use Cocoon\StorageManager\Storage;

class FileManagerTest extends TestCase
{
    protected $basePath = __DIR__;
    protected $filePath = 'test.txt';
    protected $fileContents = 'Hello, World!';

    protected function setUp(): void
    {
        if (!is_dir($this->basePath .' /tmp')) {
            mkdir($this->basePath. '/tmp', 0777, true);
        }
        Storage::init($this->basePath);
        Storage::mkdir('/tmp/copy');
    }

    protected function tearDown(): void
    {
        if (file_exists( __DIR__ . '/tmp/' . $this->filePath)) {
            unlink( __DIR__ . '/tmp/' .$this->filePath);
        }
        if (is_dir(__DIR__ . '/tmp/copy/')) {
            rmdir(__DIR__ . '/tmp/copy/');
        }
        if (is_dir($this->basePath. '/tmp/')) {
            rmdir($this->basePath. '/tmp/');
        }
    }

    public function testFileCreation()
    {
        $fileManager = Storage::file('/tmp/test.txt');
        $fileManager->put($this->fileContents);

        $this->assertTrue(file_exists( __DIR__ . '/tmp/' . $this->filePath));
        $this->assertEquals($this->fileContents, file_get_contents( __DIR__ . '/tmp/' . $this->filePath));
    }

    public function testFileExists()
    {
        file_put_contents( __DIR__ . '/tmp/' . $this->filePath, $this->fileContents);

        $fileManager = Storage::file('/tmp/test.txt');
        $exists = $fileManager->exists();

        $this->assertTrue($exists);
    }

    public function testFileReading()
    {
        file_put_contents( __DIR__ . '/tmp/' . $this->filePath, $this->fileContents);

        $fileManager = Storage::file('/tmp/test.txt');
        $contents = $fileManager->get();

        $this->assertEquals($this->fileContents, $contents);
    }
/*
    public function testFileCopy()
    {
        $newPath = 'test_copy.txt';
        file_put_contents( __DIR__ . '/tmp/' . $this->filePath, $this->fileContents);
        //Storage::mkdir('copy');
        $fileManager = Storage::file('/tmp/test.txt');
        $fileManager->copy( __DIR__ . '/tmp/copy/' . $newPath);

        $this->assertTrue(file_exists( __DIR__ . '/tmp/copy/' . $newPath));
        $this->assertEquals($this->fileContents, file_get_contents( __DIR__ . '/tmp/copy/' . $newPath));

        unlink(__DIR__ . '/tmp/copy/' . $newPath);
    }

    public function testFileMove()
    {
        $newPath = 'test_move.txt';
        file_put_contents( __DIR__ . '/tmp/' . $this->filePath, $this->fileContents);

        $fileManager = Storage::file('/tmp/test.txt');
        $fileManager->move( __DIR__ . '/tmp/copy/' . $newPath);

        $this->assertFalse(file_exists(__DIR__ . '/tmp/' . $this->filePath));
        $this->assertTrue(file_exists( __DIR__ . '/tmp/copy/' . $newPath));
        $this->assertEquals($this->fileContents, file_get_contents( __DIR__ . '/tmp/copy/' . $newPath));

        unlink( __DIR__ . '/tmp/copy/' . $newPath);
        Storage::rmdir('copy');
    }*/

    public function testFileDeletion()
    {
        file_put_contents(__DIR__ . '/tmp/' . $this->filePath, $this->fileContents);

        $fileManager = Storage::file('/tmp/test.txt');
        $fileManager->delete();

        $this->assertFalse(file_exists( __DIR__ . '/tmp/' . $this->filePath));
    }
}

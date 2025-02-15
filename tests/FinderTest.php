<?php

use PHPUnit\Framework\TestCase;
use League\Flysystem\Filesystem;
use Cocoon\StorageManager\Storage;
use League\Flysystem\Local\LocalFilesystemAdapter;

class FinderTest extends TestCase
{
    protected $basePath = __DIR__ . '/tests';
    protected $filesystem;
    public function setUp(): void
    {
        $adapter = new LocalFilesystemAdapter($this->basePath);
        $this->filesystem = new Filesystem($adapter);
        Storage::init($this->basePath);
    }

    public function testFinder()
    {
        $test = Storage::find()->in('/storage');
        $this->assertEquals('5', $test->count());
    }

    public function testHasresults()
    {
        $test = Storage::find()->in('/storage');
        $this->assertTrue($test->hasResults());
    }
}



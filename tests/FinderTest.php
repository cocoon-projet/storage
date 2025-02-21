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
        Storage::put('storage/cache/file1.txt', 'contents1');
        Storage::put('storage/cache/file2.txt', 'contents2');
        Storage::put('storage/cache/file3.txt', 'contents3'); 
        Storage::put('storage/cache/file1.php', '<?php echo "contents1";');
        Storage::put('storage/cache/file2.php', '<?php echo "contents2";'); 
        $test = Storage::find()->in('storage/cache');
        $this->assertEquals('5', $test->count());
    }

    public function testHasresults()
    {
        $test = Storage::find()->in('/storage');
        $this->assertTrue($test->hasResults());
    }

    public function testToArray()
    {
        $test = Storage::find()->in('storage/cache');
        $this->assertIsArray($test->toArray());
    }

    public function testGetIterator()
    {
        $test = Storage::find()->in('storage/cache');
        $this->assertInstanceOf(Iterator::class, $test->getIterator());
    }

    public function testFinderFiles()
    {
        $test = Storage::find()->files()->in('storage/cache');
        $this->assertEquals('5', $test->count());
    }

    public function testFinderDirectories()
    {
        Storage::mkdir('storage/dir1');
        $test = Storage::find()->directories()->in('storage');
        $this->assertEquals('2', $test->count());
    }

    public function testOnlyFilter()
    {
        $test = Storage::find()->only(['txt'])->in('storage/cache');
        $this->assertEquals('3', $test->count());
    }

    public function testExceptFilter()
    {
        $test = Storage::find()->except(['txt'])->in('storage/cache');
        $this->assertEquals('2', $test->count());
    }

    public function testSizeFilter()
    {
        $test = Storage::find()->files()->size('< 25')->in('storage/cache');
        $this->assertEquals('5', $test->count());
    } 
    
    public function testDateFilter()
    {
        $test = Storage::find()->files()->date('after 2021-01-01')->in('storage/cache');
        $this->assertEquals('5', $test->count());
    }
}



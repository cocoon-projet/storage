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
        $test = Storage::find()->in('storage/cache')->get();
        $this->assertEquals('5', count($test));
    }

    public function testHasresults()
    {
        $test = Storage::find()->in('/storage')->get();
        $this->assertTrue(!is_null($test));
    }

    public function testToArray()
    {
        $test = Storage::find()->in('storage/cache')->get();
        $this->assertIsArray($test);
    }

    public function testGetIterator()
    {
        $test = Storage::find()->in('storage/cache');
        $this->assertInstanceOf(Iterator::class, $test->getIterator());
    }

    public function testFinderFiles()
    {
        $test = Storage::find()->files()->in('storage/cache')->get();
        $this->assertEquals('5', count($test));
    }

    public function testFinderDirectories()
    {
        Storage::mkdir('storage/dir1');
        $test = Storage::find()->directories()->in('storage')->get();
        $this->assertEquals('2', count($test));
    }

    public function testOnlyFilter()
    {
        $test = Storage::find()->only(['txt'])->in('storage/cache')->get();
        $this->assertEquals('3', count($test));
    }

    public function testExceptFilter()
    {
        $test = Storage::find()->except(['txt'])->in('storage/cache')->get();
        $this->assertEquals('2', count($test));
    }

    public function testSizeFilter()
    {
        $test = Storage::find()->files()->size('< 25')->in('storage/cache')->get();
        $this->assertEquals('5', count($test));
    } 
    
    public function testDateFilter()
    {
        $test = Storage::find()->files()->date('after 2021-01-01')->in('storage/cache')->get();
        $this->assertEquals('5', count($test));
    }
}



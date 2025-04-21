<?php

use PHPUnit\Framework\TestCase;
use Cocoon\StorageManager\Storage;
use Cocoon\StorageManager\FileManager;
use Cocoon\StorageManager\Finder;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Cocoon\StorageManager\StorageConfig;

class StorageTest extends TestCase
{
    protected $basePath = __DIR__ . '/tests/storage/';
    protected $filesystem;

    protected function setUp(): void
    {
        if (!is_dir($this->basePath)) {
            mkdir($this->basePath, 0755, true);
        }
        $config = new StorageConfig($this->basePath, [
            'visibility' => 'public',
            'directory_visibility' => 'public',
            'case_sensitive' => true,
        ]);
        Storage::init($config);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->basePath)) {
            $this->rrmdir($this->basePath);
        }
    }

    protected function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        $this->rrmdir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
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
        $this->assertEquals('Hello, World!', Storage::get('test.txt'));
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
        $this->assertEquals('Hello, World!', Storage::get('copy_test.txt'));
    }

    public function testMove()
    {
        Storage::put('test.txt', 'Hello, World!');
        Storage::move('test.txt', 'moved_test.txt');
        $this->assertTrue(Storage::exists('moved_test.txt'));
        $this->assertFalse(Storage::exists('test.txt'));
        $this->assertEquals('Hello, World!', Storage::get('moved_test.txt'));
    }

    public function testExists()
    {
        Storage::put('test.txt', 'Hello, World!');
        $this->assertTrue(Storage::exists('test.txt'));
        $this->assertFalse(Storage::exists('nonexistent.txt'));
    }

    public function testMkdir()
    {
        Storage::mkdir('test_directory');
        $this->assertTrue(Storage::exists('test_directory'));
    }

    public function testRmdir()
    {
        Storage::mkdir('test_directory');
        Storage::rmdir('test_directory');
        $this->assertFalse(Storage::exists('test_directory'));
    }

    public function testFinderFiles()
    {
        Storage::put('test1.txt', 'Content 1');
        Storage::put('test2.txt', 'Content 2');
        Storage::mkdir('subdir');
        Storage::put('subdir/test3.txt', 'Content 3');

        $files = Storage::find()->files()->get();
        $this->assertCount(3, $files);
    }

    public function testFinderDirectories()
    {
        Storage::mkdir('dir1');
        Storage::mkdir('dir2');
        Storage::put('test.txt', 'Content');

        $dirs = Storage::find()->directories()->get();
        $this->assertCount(2, $dirs);
    }

    public function testFinderSize()
    {
        Storage::put('small.txt', 'Small content');
        Storage::put('large.txt', str_repeat('Large content', 100));

        $smallFiles = Storage::find()->size('< 1KB')->get();
        $this->assertCount(1, $smallFiles);
        $this->assertEquals('small.txt', $smallFiles[0]->name());
    }

    public function testFinderDate()
    {
        Storage::put('old.txt', 'Old content');
        touch($this->basePath . 'old.txt', strtotime('-2 days'));
        Storage::put('new.txt', 'New content');

        $recentFiles = Storage::find()->date('> 1 day')->get();
        $this->assertCount(1, $recentFiles);
        $this->assertEquals('new.txt', $recentFiles[0]->name());
    }

    public function testFinderOnly()
    {
        Storage::put('test1.txt', 'Content 1');
        Storage::put('test2.txt', 'Content 2');
        Storage::put('test3.php', 'Content 3');

        $txtFiles = Storage::find()->only(['*.txt'])->get();
        $this->assertCount(2, $txtFiles);
    }

    public function testFinderExcept()
    {
        Storage::put('test1.txt', 'Content 1');
        Storage::put('test2.txt', 'Content 2');
        Storage::put('test3.php', 'Content 3');

        $nonPhpFiles = Storage::find()->except(['*.php'])->get();
        $this->assertCount(2, $nonPhpFiles);
    }

    public function testFinderSort()
    {
        Storage::put('b.txt', 'B');
        Storage::put('a.txt', 'A');
        Storage::put('c.txt', 'C');

        $sortedFiles = Storage::find()->sortByName()->get();
        $this->assertEquals('a.txt', $sortedFiles[0]->name());
        $this->assertEquals('b.txt', $sortedFiles[1]->name());
        $this->assertEquals('c.txt', $sortedFiles[2]->name());
    }
}

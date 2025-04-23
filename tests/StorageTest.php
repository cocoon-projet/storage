<?php

declare(strict_types=1);

namespace Cocoon\StorageManager\Tests;

use PHPUnit\Framework\TestCase;
use Cocoon\StorageManager\Storage;
use Cocoon\StorageManager\FileManager;
use Cocoon\StorageManager\Finder;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Cocoon\StorageManager\StorageConfig;
use Cocoon\StorageManager\Exceptions\ValidationException;

class StorageTest extends TestCase
{
    private string $testDir;
    private StorageConfig $config;

    protected function setUp(): void
    {
        $this->testDir = __DIR__ . '/../storage_test';
        $this->config = new StorageConfig($this->testDir, [
            'visibility' => 'public',
            'directory_visibility' => 'public',
            'case_sensitive' => true,
        ]);

        // Création du dossier de test s'il n'existe pas
        if (!is_dir($this->testDir)) {
            mkdir($this->testDir, 0755, true);
        }

        Storage::init($this->config);
    }

    protected function tearDown(): void
    {
        // Nettoyage du dossier de test
        $this->removeDirectory($this->testDir);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function testCreateAndListDirectories(): void
    {
        $testDir = 'test_directories';
        
        // Nettoyage du dossier de test s'il existe
        if (Storage::exists($testDir)) {
            Storage::rmdir($testDir);
        }

        // Création des dossiers dans le répertoire de test
        Storage::mkdir($testDir . '/documents');
        Storage::mkdir($testDir . '/images');
        Storage::mkdir($testDir . '/cache');

        // Vérification de l'existence des dossiers
        $directories = Storage::find()->directories()->in($testDir)->get();
        $this->assertCount(3, $directories);
        $this->assertTrue(in_array('documents', array_map(fn($d) => $d->name(), $directories)));
        $this->assertTrue(in_array('images', array_map(fn($d) => $d->name(), $directories)));
        $this->assertTrue(in_array('cache', array_map(fn($d) => $d->name(), $directories)));

        // Nettoyage final
        Storage::rmdir($testDir);
    }

    public function testCreateAndListFiles(): void
    {
        // Nettoyage des fichiers existants
        if (Storage::exists('documents/rapport.txt')) Storage::delete('documents/rapport.txt');
        if (Storage::exists('documents/notes.md')) Storage::delete('documents/notes.md');
        if (Storage::exists('cache/temp.log')) Storage::delete('cache/temp.log');

        // Création des fichiers
        Storage::put('documents/rapport.txt', 'Contenu du rapport');
        Storage::put('documents/notes.md', '# Notes importantes');
        Storage::put('cache/temp.log', 'Log temporaire');

        // Vérification des fichiers dans documents
        $files = Storage::find()
            ->files()
            ->in('documents')
            ->get();

        $this->assertCount(3, $files);
        $this->assertTrue(in_array('rapport.txt', array_map(fn($f) => $f->name(), $files)));
        $this->assertTrue(in_array('notes.md', array_map(fn($f) => $f->name(), $files)));

        // Vérification des fichiers dans cache
        $files = Storage::find()
            ->files()
            ->in('cache')
            ->get();

        $this->assertCount(1, $files);
        $this->assertTrue(in_array('temp.log', array_map(fn($f) => $f->name(), $files)));
    }

    public function testFileOperations(): void
    {
        $testDir = 'test_operations';
        if (Storage::exists($testDir)) {
            Storage::rmdir($testDir);
        }
        Storage::mkdir($testDir);

        // Création d'un fichier
        Storage::put($testDir . '/test.txt', 'Contenu initial');
        $this->assertTrue(Storage::exists($testDir . '/test.txt'));
        $this->assertEquals('Contenu initial', Storage::get($testDir . '/test.txt'));

        // Copie du fichier
        Storage::copy($testDir . '/test.txt', $testDir . '/test_copy.txt');
        $this->assertTrue(Storage::exists($testDir . '/test_copy.txt'));
        $this->assertEquals('Contenu initial', Storage::get($testDir . '/test_copy.txt'));

        // Déplacement du fichier
        Storage::move($testDir . '/test.txt', $testDir . '/documents/test.txt');
        $this->assertFalse(Storage::exists($testDir . '/test.txt'));
        $this->assertTrue(Storage::exists($testDir . '/documents/test.txt'));

        // Suppression du fichier
        Storage::delete($testDir . '/test_copy.txt');
        $this->assertFalse(Storage::exists($testDir . '/test_copy.txt'));

        Storage::rmdir($testDir);
    }

    public function testDateFilter(): void
    {
        $testDir = 'test_date';
        if (Storage::exists($testDir)) {
            Storage::rmdir($testDir);
        }
        Storage::mkdir($testDir);

        // Création d'un fichier récent
        Storage::put($testDir . '/recent.txt', 'Fichier récent');

        // Test du filtre de date
        $recentFiles = Storage::find()
            ->files()
            ->in($testDir)
            ->date('> 1 day')
            ->get();

        $this->assertCount(1, $recentFiles);
        if (count($recentFiles) > 0) {
            $this->assertEquals('recent.txt', $recentFiles[0]->name());
        }

        Storage::rmdir($testDir);
    }

    public function testSizeFilter(): void
    {
        $testDir = 'test_size';
        if (Storage::exists($testDir)) {
            Storage::rmdir($testDir);
        }
        Storage::mkdir($testDir);

        // Création d'un petit fichier
        Storage::put($testDir . '/small.txt', 'Petit');
        
        // Création d'un grand fichier
        Storage::put($testDir . '/large.txt', str_repeat('Grand ', 1000));

        // Test du filtre de taille
        $smallFiles = Storage::find()
            ->files()
            ->in($testDir)
            ->size('< 1KB')
            ->get();

        $this->assertCount(1, $smallFiles);
        if (count($smallFiles) > 0 && isset($smallFiles[0])) {
            $this->assertEquals('small.txt', $smallFiles[0]->name());
        }

        Storage::rmdir($testDir);
    }

    public function testExtensionFilter(): void
    {
        $testDir = 'test_extension';
        if (Storage::exists($testDir)) {
            Storage::rmdir($testDir);
        }
        Storage::mkdir($testDir);

        // Création des fichiers
        Storage::put($testDir . '/test.txt', 'Fichier texte');
        Storage::put($testDir . '/test.md', 'Fichier markdown');
        Storage::put($testDir . '/test.log', 'Fichier log');

        // Test du filtre d'extension
        $textFiles = Storage::find()
            ->files()
            ->in($testDir)
            ->only(['txt'])
            ->get();

        $this->assertCount(1, $textFiles);
        if (count($textFiles) > 0) {
            $this->assertEquals('test.txt', $textFiles[0]->name());
        }

        Storage::rmdir($testDir);
    }

    public function testSorting(): void
    {
        // Création de fichiers avec des noms différents
        Storage::put('c.txt', 'C');
        Storage::put('a.txt', 'A');
        Storage::put('b.txt', 'B');

        // Test du tri par nom
        $sortedFiles = Storage::find()
            ->files()
            ->sortByName()
            ->get();

        $this->assertEquals('a.txt', $sortedFiles[0]->name());
        $this->assertEquals('b.txt', $sortedFiles[1]->name());
        $this->assertEquals('c.txt', $sortedFiles[2]->name());
    }

    public function testInvalidDateExpression(): void
    {
        $this->expectException(ValidationException::class);
        
        Storage::find()
            ->files()
            ->date('invalid expression')
            ->get();
    }

    public function testInvalidSizeExpression(): void
    {
        $this->expectException(ValidationException::class);
        
        Storage::find()
            ->files()
            ->size('invalid size')
            ->get();
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
        $adapter = new LocalFilesystemAdapter($this->testDir);
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

    public function testFinderFiles(): void
    {
        Storage::put('test1.txt', 'Content 1');
        Storage::put('test2.txt', 'Content 2');
        Storage::mkdir('subdir');
        Storage::put('subdir/test3.txt', 'Content 3');

        $files = Storage::find()
            ->files()
            ->get();

        $this->assertCount(3, $files);
    }

    public function testFinderDirectories(): void
    {
        Storage::mkdir('dir1');
        Storage::mkdir('dir2');
        Storage::put('test.txt', 'Content');

        $dirs = Storage::find()
            ->directories()
            ->get();

        $this->assertCount(2, $dirs);
    }

    public function testFinderSize(): void
    {
        $testDir = 'test_finder_size';
        if (Storage::exists($testDir)) {
            Storage::rmdir($testDir);
        }
        Storage::mkdir($testDir);

        Storage::put($testDir . '/small.txt', 'Small content');
        Storage::put($testDir . '/large.txt', str_repeat('Large content', 100));

        $smallFiles = Storage::find()
            ->files()
            ->in($testDir)
            ->size('< 1KB')
            ->get();

        $this->assertCount(1, $smallFiles);
        if (count($smallFiles) > 0 && isset($smallFiles[0])) {
            $this->assertEquals('small.txt', $smallFiles[0]->name());
        }

        Storage::rmdir($testDir);
    }

    public function testFinderDate(): void
    {
        Storage::file('old.txt')->write('Old content');
        touch($this->testDir . '/old.txt', strtotime('-2 days'));
        Storage::file('new.txt')->write('New content');

        $recentFiles = Storage::find()
            ->files()
            ->date('> 1 day')
            ->get();

        $this->assertCount(1, $recentFiles);
        if (count($recentFiles) > 0) {
            $this->assertEquals('new.txt', $recentFiles[0]->name());
        }
    }

    public function testFinderOnly(): void
    {
        $testDir = 'test_only';
        if (Storage::exists($testDir)) {
            Storage::rmdir($testDir);
        }
        Storage::mkdir($testDir);

        Storage::put($testDir . '/test1.txt', 'Content 1');
        Storage::put($testDir . '/test2.txt', 'Content 2');
        Storage::put($testDir . '/test3.php', 'Content 3');

        $txtFiles = Storage::find()
            ->files()
            ->in($testDir)
            ->only(['txt'])
            ->get();

        $this->assertCount(2, $txtFiles);
        if (count($txtFiles) > 0) {
            $this->assertEquals('test1.txt', $txtFiles[0]->name());
            $this->assertEquals('test2.txt', $txtFiles[1]->name());
        }

        Storage::rmdir($testDir);
    }

    public function testFinderExcept(): void
    {
        $testDir = 'test_except';
        if (Storage::exists($testDir)) {
            Storage::rmdir($testDir);
        }
        Storage::mkdir($testDir);

        Storage::put($testDir . '/test1.txt', 'Content 1');
        Storage::put($testDir . '/test2.txt', 'Content 2');
        Storage::put($testDir . '/test3.php', 'Content 3');

        $nonPhpFiles = Storage::find()
            ->files()
            ->in($testDir)
            ->except(['php'])
            ->get();

        $this->assertCount(2, $nonPhpFiles);
        if (count($nonPhpFiles) > 0) {
            $this->assertEquals('test1.txt', $nonPhpFiles[0]->name());
            $this->assertEquals('test2.txt', $nonPhpFiles[1]->name());
        }

        Storage::rmdir($testDir);
    }

    public function testFinderSort(): void
    {
        Storage::file('b.txt')->write('B');
        Storage::file('a.txt')->write('A');
        Storage::file('c.txt')->write('C');

        $sortedFiles = Storage::find()
            ->files()
            ->sortByName()
            ->get();

        $this->assertCount(3, $sortedFiles);
        if (count($sortedFiles) > 0) {
            $this->assertEquals('a.txt', $sortedFiles[0]->name());
            $this->assertEquals('b.txt', $sortedFiles[1]->name());
            $this->assertEquals('c.txt', $sortedFiles[2]->name());
        }
    }
}

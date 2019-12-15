<?php

namespace WatchTower\Tests;

use ErrorException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use WatchTower\Events\ExceptionEvent;
use WatchTower\Exceptions\WatchTowerException;
use WatchTower\Outputs\File;
use WatchTower\Outputs\OutputTargetInterface;

class FileTest extends TestCase
{
    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $dir = $this->getFileConfig()['file.dir'] . '/';
        if (file_exists($dir)) {
            $this->rrmdir($dir);
        }
    }

    public function rrmdir($dir)
    {
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            is_dir($file) ? $this->rrmdir($file) : unlink($file);
        }
        rmdir($dir);
    }

    public function getSampleOutputStack()
    {
        return [
            'handler' => [
                'main' => '',
                'minibox' => '',
                'plaintext' => '
                     ErrorException: Undefined variable: a in file C:\a\b\c\include.php on line 8
                     Stack trace:
                      1. ErrorException->() C:\a\b\c\include.php:8
                      2. WatchTower\WatchTower->WatchTower\{closure}() C:\a\b\c\include.php:8
                      3. include_once()  C:\a\b\c\include\functional_test.php:10'
            ],
            'targets' => [
                'browser' => ''
            ]
        ];
    }


    protected function getFileConfig()
    {
        $conFile = [
            'watchtower.reader' => 'http://watchtower.local/reader.php',
            'file.dir' => WATCHTOWER_TROOT . '/files/exceptions',
            'file.ttl' => '1 week'
        ];
        return $conFile;
    }

    /**
     * @throws WatchTowerException
     */
    public function test__construct()
    {
        $o = new File($this->getFileConfig());
        $this->assertInstanceOf(File::class, $o);
    }

    /**
     * @return File|OutputTargetInterface
     * @throws WatchTowerException
     */
    public function testCreate()
    {
        $o = File::create($this->getFileConfig());
        $this->assertInstanceOf(File::class, $o);
        return $o;
    }

    /**
     * @depends testCreate
     * @param File $o
     * @throws ReflectionException
     */
    public function testGetFilename(File $o)
    {
        $exception = new ErrorException('Testing message', 1, 1);
        $e = new ExceptionEvent($exception);
        $fn = $o->getFilename($e->getId());
        $this->assertIsString($fn);
        $this->assertNotEmpty($fn);
        $this->assertStringContainsString($e->getId(), $fn);
    }

    /**
     * @depends testCreate
     * @param File $o
     */
    public function testGetName(File $o)
    {
        $name = $o->getName();
        $this->assertIsString($name);
        $this->assertNotEmpty($name);
    }

    /**
     * @depends testCreate
     * @param File $o
     * @return File
     * @throws ReflectionException
     * @throws WatchTowerException
     */
    public function testExecute(File $o)
    {
        $exception = new ErrorException('Testing message', 1, 1);
        $e = new ExceptionEvent($exception);
        $o->execute($e, 'Output this string in File', $this->getSampleOutputStack());
        $out = $o->getOutput();
        $this->assertIsArray($out);
        $this->assertArrayHasKey('success', $out);
        $this->assertArrayHasKey('accessLink', $out);
        $this->assertTrue($out['success']);
        $this->assertNotEmpty($out['accessLink']);
        $filepath = $this->getFileConfig()['file.dir'] . '/' . $o->getFilename($e->getId());
        $this->assertFileExists($filepath);
        $this->assertFileIsReadable($filepath);
        $this->assertStringEqualsFile($filepath, 'Output this string in File');
        return $o;
    }

}

<?php

namespace WatchTower\Tests;

use PHPUnit\Framework\TestCase;
use WatchTower\Events\ExceptionEvent;
use WatchTower\Exceptions\WatchTowerException;
use WatchTower\Outputs\File;
use WatchTower\Outputs\OutputTargetInterface;

class FileTest extends TestCase
{
    /**
     * @return void
     */
    protected function tearDown() : void
    {
        $dir = $this->getFileConfig()['dir'].'/';
        if(file_exists($dir)) {
            $this->rrmdir($dir);
        }
    }

    public function rrmdir($dir) {
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            is_dir($file) ? $this->rrmdir($file) : unlink($file);
        }
        rmdir($dir);
    }


    protected function getFileConfig() {
        $conFile = [
            'dir'     => __DIR__.'/tests/files'
        ];
        return $conFile;
    }
    /**
     * @throws WatchTowerException
     */
    public function test__construct()
    {
        $o = new File($this->getFileConfig());
        $this->assertInstanceOf(File::class,$o);
    }
    /**
     * @return File|OutputTargetInterface
     */
    public function testCreate()
    {
        $o = File::create($this->getFileConfig());
        $this->assertInstanceOf(File::class,$o);
        return $o;
    }

    /**
     * @depends testCreate
     * @param File $o
     */
    public function testGetFilename(File $o) {
        $exception = new \ErrorException('Testing message',1,1);
        $e = new ExceptionEvent($exception);
        $fn = $o->getFilename($e->getId());
        $this->assertIsString($fn);
        $this->assertNotEmpty($fn);
        $this->assertStringContainsString($e->getId(),$fn);
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
     * @throws WatchTowerException
     */
    public function testExecute(File $o)
    {
        $exception = new \ErrorException('Testing message',1,1);
        $e = new ExceptionEvent($exception);
        $o->execute($e,'Output this string in File',['plaintext'=>'Output plaintext']);
        $ov = $o->getOutputVars();
        $this->assertIsArray($ov);
        $this->assertArrayHasKey('success',$ov);
        $this->assertArrayHasKey('fileAccessLink',$ov);
        $this->assertTrue($ov['success']);
        $this->assertNotEmpty($ov['fileAccessLink']);
        $filepath = $this->getFileConfig()['dir'].'/'.$o->getFilename($e->getId());
        $this->assertFileExists($filepath);
        $this->assertFileIsReadable($filepath);
        $this->assertStringEqualsFile($filepath,'Output this string in File');
        return $o;
    }

    /**
     * @depends testCreate
     * @param File $o
     */
    public function testGetDefaultConfig(File $o)
    {
        $dc = $o->getDefaultConfig();
        $this->assertIsArray($dc);
        $this->assertNotEmpty($dc);
        $keys = ['dir','ttl','accessLink'];
        foreach($keys as $key) {
            $this->assertArrayHasKey($key,$dc);
        }
    }
    /**
     * @depends testExecute
     * @param File $o
     */
    public function testGetOutputVars(File $o)
    {
        $ov = $o->getOutputVars();
        $this->assertIsArray($ov);
        $this->assertNotEmpty($ov);
        $this->assertTrue($ov['success']);
        $this->assertNotEmpty($ov['fileAccessLink']);
    }
}

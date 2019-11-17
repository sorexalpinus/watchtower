<?php

namespace WatchTower\Tests;

use PHPUnit\Framework\TestCase;
use WatchTower\Events\ExceptionEvent;
use WatchTower\Outputs\Browser;

class BrowserTest extends TestCase
{
    /**
     * @throws \WatchTower\Exceptions\WatchTowerException
     */
    public function test__construct()
    {
        $o = new Browser();
        $this->assertInstanceOf(Browser::class,$o);
    }

    /**
     * @return Browser|\WatchTower\Outputs\OutputTargetInterface
     */
    public function testCreate()
    {
        $o = Browser::create();
        $this->assertInstanceOf(Browser::class,$o);
        return $o;
    }

    /**
     * @depends testCreate
     * @param Browser $o
     */
    public function testGetName(Browser $o)
    {
        $name = $o->getName();
        $this->assertIsString($name);
        $this->assertNotEmpty($name);
    }

    /**
     * @depends testCreate
     * @param Browser $o
     * @throws \WatchTower\Exceptions\WatchTowerException
     */
    public function testExecute(Browser $o)
    {
        $exception = new \ErrorException('Testing message',1,1);
        $e = new ExceptionEvent($exception);
        $this->expectOutputString('Output this string in browser');
        $o->execute($e,'Output this string in browser');
    }

    /**
     * @depends testCreate
     * @param Browser $o
     */
    public function testGetDefaultConfig(Browser $o)
    {
        $dc = $o->getDefaultConfig();
        $this->assertIsArray($dc);
        $this->assertEmpty($dc);
    }
    /**
     * @depends testCreate
     * @param Browser $o
     */
    public function testGetOutputVars(Browser $o)
    {
        $ov = $o->getOutputVars();
        $this->assertIsArray($ov);
        $this->assertEmpty($ov);
    }
}

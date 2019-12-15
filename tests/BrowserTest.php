<?php

namespace WatchTower\Tests;

use ErrorException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use WatchTower\Events\ExceptionEvent;
use WatchTower\Exceptions\WatchTowerException;
use WatchTower\Outputs\Browser;
use WatchTower\Outputs\OutputTargetInterface;
use WatchTower\WatchTower;

class BrowserTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
        WatchTower::create([]);
    }

    /**
     * @throws WatchTowerException
     */
    public function test__construct()
    {
        $o = new Browser();
        $this->assertInstanceOf(Browser::class, $o);
    }

    /**
     * @return Browser|OutputTargetInterface
     * @throws WatchTowerException
     */
    public function testCreate()
    {
        $o = Browser::create();
        $this->assertInstanceOf(Browser::class, $o);
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
     * @throws ReflectionException
     */
    public function testExecute(Browser $o)
    {
        $exception = new ErrorException('Testing message', 1, 1);
        $e = new ExceptionEvent($exception);
        $this->expectOutputString('Output this string in browser');
        $o->execute($e, 'Output this string in browser');
    }

}

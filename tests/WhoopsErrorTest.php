<?php

namespace Tests;

use ErrorException;
use PHPUnit\Framework\TestCase;
use WatchTower\Events\ExceptionEvent;
use WatchTower\Exceptions\WatchTowerException;
use WatchTower\Handlers\HandlerInterface;
use WatchTower\Handlers\WhoopsError;
use WatchTower\Outputs\Browser;
use WatchTower\WatchTower;

/**
 * Class SomethingWentWrongTest
 *
 * @covers \WatchTower\Handlers\WhoopsError
 * @package WatchTower\Tests
 */
class WhoopsErrorTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        WatchTower::create([]);
    }
    /**
     * @return WhoopsError
     * @throws WatchTowerException
     */
    public function test__construct()
    {
        $h = new WhoopsError();
        $this->assertInstanceOf(HandlerInterface::class, $h);
        $this->assertInstanceOf(WhoopsError::class, $h);
        return $h;

    }

    /**
     * @return HandlerInterface|WhoopsError
     * @throws WatchTowerException
     */
    public function testCreate()
    {
        $h = WhoopsError::create();
        $this->assertInstanceOf(HandlerInterface::class, $h);
        $this->assertInstanceOf(WhoopsError::class, $h);
        return $h;
    }

    /**
     * @depends testCreate
     * @param WhoopsError $h
     * @return WhoopsError
     * @throws WatchTowerException
     */
    public function testSendTo(WhoopsError $h)
    {
        $h->sendTo(Browser::create());
        $ot = $h->getOutputTargets();
        $this->assertIsArray($ot);
        $this->assertInstanceOf(Browser::class, $ot[0]);
        return $h;
    }

    /**
     * @depends testSendTo
     * @param WhoopsError $h
     * @return WhoopsError
     * @throws \ReflectionException
     */
    public function testHandle(WhoopsError $h)
    {
        $regex = '/Testing message/';
        $regex2 = '/Environment &amp; details:/';
        $exception = new ErrorException('Testing message', 1, 1);
        $e = new ExceptionEvent($exception);
        $h->handle($e);
        $o = $h->getOutput();
        $this->assertNotEmpty($o);
        $this->assertRegExp($regex, $o);
        $this->assertRegExp($regex2, $o);
        $this->assertNotSame(strip_tags($o), $o);
        return $h;
    }

    /**
     * @depends testHandle
     * @param WhoopsError $h
     * @return WhoopsError
     * @throws \ReflectionException
     */
    public function testSendToOutputTargets(WhoopsError $h)
    {
        $regex = '/Testing message/';
        $regex2 = '/Environment &amp; details:/';
        $exception = new ErrorException('Testing message', 1, 1);
        $e = new ExceptionEvent($exception);
        $this->expectOutputRegex($regex);
        $this->expectOutputRegex($regex2);
        $h->sendToOutputTargets($e);
        return $h;
    }

    /**
     * @depends testCreate
     * @param WhoopsError $h
     */
    public function testGetName(WhoopsError $h)
    {
        $name = $h->getName();
        $this->assertIsString($name);
        $this->assertNotEmpty($name);
        $this->assertSame(get_class($h), $name);
    }
}

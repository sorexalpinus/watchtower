<?php

namespace Tests;

use ErrorException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use WatchTower\Events\ExceptionEvent;
use WatchTower\Exceptions\WatchTowerException;
use WatchTower\Handlers\HandlerInterface;
use WatchTower\Handlers\WhoopsMinibox;
use WatchTower\Outputs\Browser;

class WhoopsMiniboxTest extends TestCase
{

    /**
     * @return WhoopsMinibox
     */
    public function test__construct()
    {
        $h = new WhoopsMinibox();
        $this->assertInstanceOf(HandlerInterface::class, $h);
        $this->assertInstanceOf(WhoopsMinibox::class, $h);
        return $h;

    }

    /**
     * @return HandlerInterface|WhoopsMinibox
     */
    public function testCreate()
    {
        $h = WhoopsMinibox::create();
        $this->assertInstanceOf(HandlerInterface::class, $h);
        $this->assertInstanceOf(WhoopsMinibox::class, $h);
        return $h;
    }

    /**
     * @depends testCreate
     * @param WhoopsMinibox $h
     * @return WhoopsMinibox
     * @throws WatchTowerException
     */
    public function testSendTo(WhoopsMinibox $h)
    {
        $h->sendTo(Browser::create());
        $ot = $h->getOutputTargets();
        $this->assertIsArray($ot);
        $this->assertInstanceOf(Browser::class, $ot[0]);
        return $h;
    }

    /**
     * @depends testSendTo
     * @param WhoopsMinibox $h
     * @return WhoopsMinibox
     * @throws ReflectionException
     * @throws WatchTowerException
     */
    public function testHandle(WhoopsMinibox $h)
    {
        $regex = '/Testing message/';
        $regex2 = '/wt-minibox-wrapper/';
        $regex3 = '/<button action="expand"/';
        $exception = new ErrorException('Testing message', 1, 1);
        $e = new ExceptionEvent($exception);
        $h->handle($e);
        $o = $h->getOutput('minibox');
        $this->assertNotEmpty($o);
        $this->assertRegExp($regex, $o);
        $this->assertRegExp($regex2, $o);
        $this->assertRegExp($regex3, $o);
        $this->assertNotSame(strip_tags($o), $o);
        return $h;
    }

    /**
     * @depends testHandle
     * @param WhoopsMinibox $h
     * @return WhoopsMinibox
     * @throws ReflectionException
     */
    public function testSendToOutputTargets(WhoopsMinibox $h)
    {
        $regex = '/Testing message/';
        $regex2 = '/Environment &amp; details:/';
        $regex3 = '/<button action="expand"/';
        $exception = new ErrorException('Testing message', 1, 1);
        $e = new ExceptionEvent($exception);
        $this->expectOutputRegex($regex);
        $this->expectOutputRegex($regex2);
        $this->expectOutputRegex($regex3);
        $h->sendToOutputTargets($e);
        return $h;
    }


    /**
     * @depends testCreate
     * @param WhoopsMinibox $h
     */
    public function testGetName(WhoopsMinibox $h)
    {
        $name = $h->getName();
        $this->assertIsString($name);
        $this->assertNotEmpty($name);
        $this->assertSame(get_class($h), $name);
    }
}

<?php

namespace WatchTower\Tests;

use PHPUnit\Framework\TestCase;
use WatchTower\Events\ExceptionEvent;
use WatchTower\Exceptions\WatchTowerException;
use WatchTower\Handlers\HandlerInterface;
use WatchTower\Handlers\PlainTextNotice;
use WatchTower\Outputs\Browser;

/**
 * Class PlainTextNoticeTest
 * @covers \WatchTower\Handlers\PlainTextNotice
 * @package WatchTower\Tests
 */
class PlainTextNoticeTest extends TestCase
{
    /**
     * @return PlainTextNotice
     * @throws \WatchTower\Exceptions\WatchTowerException
     */
    public function test__construct()
    {
        $h = new PlainTextNotice();
        $this->assertInstanceOf(HandlerInterface::class,$h);
        $this->assertInstanceOf(PlainTextNotice::class,$h);
        return $h;

    }

    /**
     * @return HandlerInterface|PlainTextNotice
     * @throws \WatchTower\Exceptions\WatchTowerException
     */
    public function testCreate()
    {
        $h = PlainTextNotice::create();
        $this->assertInstanceOf(HandlerInterface::class,$h);
        $this->assertInstanceOf(PlainTextNotice::class,$h);
        return $h;
    }

    /**
     * @depends testCreate
     * @param PlainTextNotice $h
     */
    public function testGetDefaultConfig(PlainTextNotice $h)
    {
        $dc = $h->getDefaultConfig();
        $this->assertTrue(is_array($dc) or is_null($dc));
        $dc = $h->getDefaultConfig('type');
        $this->assertTrue(is_null($dc));
    }

    /**
     * @throws WatchTowerException
     */
    public function testGetConfigError()
    {
        $this->expectException(WatchTowerException::class);
        $this->expectExceptionMessage('Config item "type" is not suported');
        PlainTextNotice::create(['type'=>'extended']);
    }

    /**
     * @throws WatchTowerException
     */
    public function testGetConfig()
    {
        $h = PlainTextNotice::create();
        $conf = $h->getConfig();
        $this->assertIsArray($conf);
    }

    /**
     * @depends testCreate
     * @param PlainTextNotice $h
     * @return PlainTextNotice
     */
    public function testSendTo(PlainTextNotice $h)
    {
        $h->sendTo(Browser::create());
        $ot = $h->getOutputTargets();
        $this->assertIsArray($ot);
        $this->assertInstanceOf(Browser::class,$ot[0]);
        return $h;
    }

    /**
     * @depends testSendTo
     * @param PlainTextNotice $h
     * @return PlainTextNotice
     */
    public function testTestHandle(PlainTextNotice $h)
    {
        $regex='/ErrorException: Testing message/';
        $exception = new \ErrorException('Testing message',1,1);
        $e = new ExceptionEvent($exception);
        $h->handle($e);
        $o = $h->getOutput();
        $this->assertRegExp($regex,$o);
        return $h;
    }

    /**
     * @depends testTestHandle
     * @param PlainTextNotice $h
     * @return PlainTextNotice
     */
    public function testSendToOutputTargets(PlainTextNotice $h)
    {
        $regex='/ErrorException: Testing message/';
        $exception = new \ErrorException('Testing message',1,1);
        $e = new ExceptionEvent($exception);
        $this->expectOutputRegex($regex);
        $h->sendToOutputTargets($e);
        return $h;
    }


    /**
     * @depends testSendToOutputTargets
     * @param PlainTextNotice $h
     */
    public function testGetOutputVars(PlainTextNotice $h)
    {
        $ov = $h->getOutputVars();
        $this->assertIsArray($ov);
        $this->assertEmpty($ov);

    }

    /**
     * @depends testCreate
     * @param PlainTextNotice $h
     */
    public function testGetName(PlainTextNotice $h)
    {
        $name = $h->getName();
        $this->assertIsString($name);
        $this->assertNotEmpty($name);
        $this->assertSame(get_class($h),$name);
    }


}

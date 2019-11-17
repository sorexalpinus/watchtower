<?php

namespace WatchTower\Tests;

use PHPUnit\Framework\TestCase;
use WatchTower\Events\ExceptionEvent;
use WatchTower\Exceptions\WatchTowerException;
use WatchTower\Handlers\HandlerInterface;
use WatchTower\Handlers\SomethingWentWrong;
use WatchTower\Outputs\Browser;

/**
 * Class SomethingWentWrongTest
 * @covers \WatchTower\Handlers\SomethingWentWrong
 * @package WatchTower\Tests
 */
class SomethingWentWrongTest extends TestCase
{

    /**
     * @return SomethingWentWrong
     * @throws \WatchTower\Exceptions\WatchTowerException
     */
    public function test__construct()
    {
        $h = new SomethingWentWrong();
        $this->assertInstanceOf(HandlerInterface::class,$h);
        $this->assertInstanceOf(SomethingWentWrong::class,$h);
        return $h;

    }

    /**
     * @return HandlerInterface|SomethingWentWrong
     * @throws \WatchTower\Exceptions\WatchTowerException
     */
    public function testCreate()
    {
        $h = SomethingWentWrong::create();
        $this->assertInstanceOf(HandlerInterface::class,$h);
        $this->assertInstanceOf(SomethingWentWrong::class,$h);
        return $h;
    }

    /**
     * @depends testCreate
     * @param SomethingWentWrong $h
     */
    public function testGetDefaultConfig(SomethingWentWrong $h)
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
        SomethingWentWrong::create(['type'=>'extended']);
    }

    /**
     * @throws WatchTowerException
     */
    public function testGetConfig()
    {
        $h = SomethingWentWrong::create();
        $conf = $h->getConfig();
        $this->assertIsArray($conf);
    }

    /**
     * @depends testCreate
     * @param SomethingWentWrong $h
     * @return SomethingWentWrong
     */
    public function testSendTo(SomethingWentWrong $h)
    {
        $h->sendTo(Browser::create());
        $ot = $h->getOutputTargets();
        $this->assertIsArray($ot);
        $this->assertInstanceOf(Browser::class,$ot[0]);
        return $h;
    }

    /**
     * @depends testSendTo
     * @param SomethingWentWrong $h
     * @return SomethingWentWrong
     */
    public function testTestHandle(SomethingWentWrong $h)
    {
        $regex='/We\'re sorry, but something went wrong/';
        $exception = new \ErrorException('Testing message',1,1);
        $e = new ExceptionEvent($exception);
        $h->handle($e);
        $o = $h->getOutput();
        $this->assertRegExp($regex,$o);
        $this->assertNotSame(strip_tags($o),$o);
        return $h;
    }

    /**
     * @depends testTestHandle
     * @param SomethingWentWrong $h
     * @return SomethingWentWrong
     */
    public function testSendToOutputTargets(SomethingWentWrong $h)
    {
        $regex='/We\'re sorry, but something went wrong/';
        $exception = new \ErrorException('Testing message',1,1);
        $e = new ExceptionEvent($exception);
        $this->expectOutputRegex($regex);
        $h->sendToOutputTargets($e);
        return $h;
    }


    /**
     * @depends testSendToOutputTargets
     * @param SomethingWentWrong $h
     */
    public function testGetOutputVars(SomethingWentWrong $h)
    {
        $ov = $h->getOutputVars();
        $this->assertIsArray($ov);
        $this->assertEmpty($ov);

    }

    /**
     * @depends testCreate
     * @param SomethingWentWrong $h
     */
    public function testGetName(SomethingWentWrong $h)
    {
        $name = $h->getName();
        $this->assertIsString($name);
        $this->assertNotEmpty($name);
        $this->assertSame(get_class($h),$name);
    }
}

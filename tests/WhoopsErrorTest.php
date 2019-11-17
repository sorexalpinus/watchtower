<?php

namespace Tests;
use PHPUnit\Framework\TestCase;
use WatchTower\Events\ExceptionEvent;
use WatchTower\Exceptions\WatchTowerException;
use WatchTower\Handlers\HandlerInterface;
use WatchTower\Handlers\WhoopsError;
use WatchTower\Outputs\Browser;

/**
 * Class SomethingWentWrongTest
 * @covers \WatchTower\Handlers\WhoopsError
 * @package WatchTower\Tests
 */
class WhoopsErrorTest extends TestCase
{

    /**
     * @return WhoopsError
     * @throws WatchTowerException
     */
    public function test__construct()
    {
        $h = new WhoopsError();
        $this->assertInstanceOf(HandlerInterface::class,$h);
        $this->assertInstanceOf(WhoopsError::class,$h);
        return $h;

    }

    /**
     * @return HandlerInterface|WhoopsError
     * @throws WatchTowerException
     */
    public function testCreate()
    {
        $h = WhoopsError::create();
        $this->assertInstanceOf(HandlerInterface::class,$h);
        $this->assertInstanceOf(WhoopsError::class,$h);
        return $h;
    }

    /**
     * @depends testCreate
     * @param WhoopsError $h
     */
    public function testGetDefaultConfig(WhoopsError $h)
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
        WhoopsError::create(['type'=>'extended']);
    }

    /**
     * @throws WatchTowerException
     */
    public function testGetConfig()
    {
        $h = WhoopsError::create();
        $conf = $h->getConfig();
        $this->assertIsArray($conf);
    }

    /**
     * @depends testCreate
     * @param WhoopsError $h
     * @return WhoopsError
     */
    public function testSendTo(WhoopsError $h)
    {
        $h->sendTo(Browser::create());
        $ot = $h->getOutputTargets();
        $this->assertIsArray($ot);
        $this->assertInstanceOf(Browser::class,$ot[0]);
        return $h;
    }

    /**
     * @depends testSendTo
     * @param WhoopsError $h
     * @return WhoopsError
     */
    public function testHandle(WhoopsError $h)
    {
        $regex = '/Testing message/';
        $regex2 = '/Environment &amp; details:/';
        $exception = new \ErrorException('Testing message',1,1);
        $e = new ExceptionEvent($exception);
        $h->handle($e);
        $o = $h->getOutput();
        $this->assertNotEmpty($o);
        $this->assertRegExp($regex,$o);
        $this->assertRegExp($regex2,$o);
        $this->assertNotSame(strip_tags($o),$o);
        return $h;
    }

    /**
     * @depends testHandle
     * @param WhoopsError $h
     * @return WhoopsError
     */
    public function testSendToOutputTargets(WhoopsError $h)
    {
        $regex = '/Testing message/';
        $regex2 = '/Environment &amp; details:/';
        $exception = new \ErrorException('Testing message',1,1);
        $e = new ExceptionEvent($exception);
        $this->expectOutputRegex($regex);
        $this->expectOutputRegex($regex2);
        $h->sendToOutputTargets($e);
        return $h;
    }


    /**
     * @depends testSendToOutputTargets
     * @param WhoopsError $h
     */
    public function testGetOutputVars(WhoopsError $h)
    {
        $regexp = '/ErrorException: Testing message/';
        $ov = $h->getOutputVars();
        $this->assertIsArray($ov);
        $this->assertArrayHasKey('plaintext',$ov);
        $this->assertRegExp($regexp,$ov['plaintext']);
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
        $this->assertSame(get_class($h),$name);
    }
}

<?php

namespace WatchTower\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionException;
use WatchTower\Events\ExceptionEvent;
use WatchTower\Exceptions\WatchTowerException;
use WatchTower\Handlers\HandlerInterface;
use WatchTower\Handlers\SomethingWentWrong;
use WatchTower\Outputs\Browser;

/**
 * Class SomethingWentWrongTest
 *
 * @covers \WatchTower\Handlers\SomethingWentWrong
 * @package WatchTower\Tests
 */
class SomethingWentWrongTest extends TestCase
{

    /**
     * @return SomethingWentWrong
     */
    public function test__construct()
    {
        $h = new SomethingWentWrong();
        $this->assertInstanceOf(HandlerInterface::class, $h);
        $this->assertInstanceOf(SomethingWentWrong::class, $h);
        return $h;

    }

    /**
     * @return HandlerInterface|SomethingWentWrong
     */
    public function testCreate()
    {
        $h = SomethingWentWrong::create();
        $this->assertInstanceOf(HandlerInterface::class, $h);
        $this->assertInstanceOf(SomethingWentWrong::class, $h);
        return $h;
    }

    /**
     * @depends testCreate
     * @param SomethingWentWrong $h
     * @return SomethingWentWrong
     * @throws WatchTowerException
     */
    public function testSendTo(SomethingWentWrong $h)
    {
        $h->sendTo(Browser::create());
        $ot = $h->getOutputTargets();
        $this->assertIsArray($ot);
        $this->assertInstanceOf(Browser::class, $ot[0]);
        return $h;
    }

    /**
     * @depends testSendTo
     * @param SomethingWentWrong $h
     * @return SomethingWentWrong
     * @throws ReflectionException
     */
    public function testTestHandle(SomethingWentWrong $h)
    {
        $regex = '/We\'re sorry, but something went wrong/';
        $exception = new \ErrorException('Testing message', 1, 1);
        $e = new ExceptionEvent($exception);
        $h->handle($e);
        $o = $h->getOutput();
        $this->assertRegExp($regex, $o);
        $this->assertNotSame(strip_tags($o), $o);
        return $h;
    }

    /**
     * @depends testTestHandle
     * @param SomethingWentWrong $h
     * @return SomethingWentWrong
     * @throws ReflectionException
     */
    public function testSendToOutputTargets(SomethingWentWrong $h)
    {
        $regex = '/We\'re sorry, but something went wrong/';
        $exception = new \ErrorException('Testing message', 1, 1);
        $e = new ExceptionEvent($exception);
        $this->expectOutputRegex($regex);
        $h->sendToOutputTargets($e);
        return $h;
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
        $this->assertSame(get_class($h), $name);
    }
}

<?php

namespace WatchTower\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionException;
use WatchTower\Exceptions\WatchTowerException;
use WatchTower\Handlers\PlainTextNotice;
use WatchTower\Handlers\WhoopsMinibox;
use WatchTower\Outputs\Browser;
use WatchTower\WatchTower;

/**
 * Class WatchTowerTest
 *
 * @covers \WatchTower\WatchTower
 * @package WatchTower
 */
class WatchTowerTest extends TestCase
{

    /**
     * @return WatchTower
     * @throws WatchTowerException
     */
    public function testGetInstance()
    {
        WatchTower::create([]);
        $wt = WatchTower::getInstance();
        $this->assertInstanceOf(WatchTower::class, $wt);

        return $wt;
    }

    /**
     * @throws WatchTowerException
     */
    public function testEmptyConfig()
    {
        WatchTower::destroyInstance();
        WatchTower::create([]);
        $wt = WatchTower::getInstance();
        $this->expectException(WatchTowerException::class);
        $wt->watch();
    }

    /**
     * @depends testGetInstance
     * @param WatchTower $wt
     * @return WatchTower
     */
    public function testWatchFor(WatchTower $wt)
    {
        $wt = $wt->watchFor(E_WARNING);
        $this->assertInstanceOf(WatchTower::class, $wt);
        return $wt;

    }

    /**
     * @depends testWatchFor
     * @param WatchTower $wt
     * @return WatchTower
     * @throws WatchTowerException
     */
    public function testThenCreate(WatchTower $wt)
    {
        $wt = $wt->thenCreate(WhoopsMinibox::create());
        $this->assertInstanceOf(WatchTower::class, $wt);
        return $wt;
    }

    /**
     * @depends testThenCreate
     * @param WatchTower $wt
     * @return WatchTower
     * @throws WatchTowerException
     */
    public function testAndSendTo(WatchTower $wt)
    {
        $wt = $wt->andSendTo(Browser::create());
        $this->assertInstanceOf(WatchTower::class, $wt);
        return $wt;

    }

    /**
     * @depends testAndSendTo
     * @param WatchTower $wt
     * @return WatchTower
     * @throws WatchTowerException
     */
    public function testWatch(WatchTower $wt)
    {
        $wt->watch();
        $this->assertTrue($wt->isEnabled());
        $this->assertTrue($wt->isInitialized());

        return $wt;
    }

    /**
     * @throws WatchTowerException
     * @throws ReflectionException
     */
    public function testHandleException()
    {
        WatchTower::destroyInstance();
        WatchTower::create([]);
        $wt = WatchTower::getInstance();
        $browser = $this->createMock(Browser::class);
        $browser->method('execute')
            ->will($this->returnCallback(function () {
                echo 'exception text';
            }));
        $wt
            ->watchFor(\ErrorException::class)
            ->thenCreate(PlainTextNotice::create())
            ->andSendTo($browser)
            ->watch();
        $exception = new \ErrorException('exception', 2, 1);
        $this->expectOutputString('exception text');
        $result = $wt->handleException($exception);
        $this->assertTrue($result);
    }


    /**
     *
     * @throws WatchTowerException
     */
    public function testStateChange()
    {
        $wt = WatchTower::getInstance();
        $wt->watch();
        $this->assertTrue($wt->isEnabled());
        $this->assertTrue($wt->isInitialized());
        $wt->disable();
        $this->assertFalse($wt->isEnabled());
        $wt->enable();
        $this->assertTrue($wt->isEnabled());
    }


}

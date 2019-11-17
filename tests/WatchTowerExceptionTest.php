<?php

namespace Tests;
use PHPUnit\Framework\TestCase;
use WatchTower\Exceptions\WatchTowerException;

class WatchTowerExceptionTest extends TestCase
{

    /**
     * @return WatchTowerException
     */
    public function test__construct()
    {
        $e = new WatchTowerException('Error in watchtower itself',1,1);
        $this->assertInstanceOf(WatchTowerException::class,$e);
        return $e;
    }


    /**
     * @depends test__construct
     * @param WatchTowerException $e
     */
    public function testGetTitle(WatchTowerException $e)
    {
        $t = $e->getTitle();
        $this->assertIsString($t);
        $this->assertSame('WatchTower exception',$t);
    }

    /**
     * @depends test__construct
     * @param WatchTowerException $e
     */
    public function testHandle(WatchTowerException $e)
    {
        $result = $e->handle();
        $this->assertNotFalse($result);
    }

    /**
     * @depends test__construct
     * @param WatchTowerException $e
     */
    public function testGetExtraInfo(WatchTowerException $e)
    {
        $ei = $e->getExtraInfo();
        $this->assertEmpty($ei);
    }

    /**
     * @depends test__construct
     * @param WatchTowerException $e
     */
    public function testGetFullMessage(WatchTowerException $e) {
        $msg = $e->getFullMessage();
        $this->assertIsString($msg);
        $this->assertNotEmpty($msg);
        $this->assertStringContainsString('Error in watchtower itself',$msg);
    }
}

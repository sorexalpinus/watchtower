<?php

namespace WatchTower\Tests;

use mysqli;
use PHPUnit\Framework\TestCase;
use WatchTower\Exceptions\MysqlException;
use WatchTower\Exceptions\WatchTowerException;

class MysqlExceptionTest extends TestCase
{
    public function getMysqli() {
        return new mysqli();
    }

    /**
     * @return MysqlException
     */
    public function test__construct()
    {
        $mysqli = $this->getMysqli();
        $e = new MysqlException($mysqli,'SELECT * FROM `some_table` WHERE `some_column` > 0','Error in mysql query',1,1);
        $this->assertInstanceOf(MysqlException::class,$e);
        return $e;
    }


    /**
     * @depends test__construct
     * @param MysqlException $e
     */
    public function testGetTitle(MysqlException $e)
    {
        $t = $e->getTitle();
        $this->assertIsString($t);
        $this->assertSame('MySQL error',$t);
    }

    /**
     * @depends test__construct
     * @param MysqlException $e
     * @throws WatchTowerException
     */
    public function testHandle(MysqlException $e)
    {
        $result = $e->handle();
        $this->assertNotFalse($result);
    }

    /**
     * @depends test__construct
     * @param MysqlException $e
     */
    public function testGetExtraInfo(MysqlException $e)
    {
        $ei = $e->getExtraInfo();
        $this->assertIsArray($ei);
        $this->assertArrayHasKey('query',$ei);
        $this->assertNotEmpty($ei['query']);
    }


}

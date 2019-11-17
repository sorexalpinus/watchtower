<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use WatchTower\Events\ErrorEvent;

class ErrorEventTest extends TestCase
{
    protected $infoArr = [
        'code' => 2,
        'message' => 'Invalid argument supplied for foreach()',
        'file' => 'C:\\wamp64\\www\\personal\\watchtower\\tests\\core\\EventLauncher.php',
        'line' => 31,
        'trace' =>
            array(
                0 =>
                    array(
                        'file' => 'C:\\wamp64\\www\\personal\\watchtower\\tests\\core\\EventLauncher.php',
                        'line' => 31,
                        'function' => 'WatchTower\\{closure}',
                        'class' => 'WatchTower\\WatchTower',
                        'type' => '->',
                        'args' =>
                            array(
                                0 => 2,
                                1 => 'Invalid argument supplied for foreach()',
                                2 => 'C:\\wamp64\\www\\personal\\watchtower\\tests\\core\\EventLauncher.php',
                                3 => 31,
                                4 =>
                                    array(
                                        'type' => 2,
                                        'message' => 'E_WARNING triggered',
                                        'a' => 1,
                                    ),
                            ),
                    ),
                1 =>
                    array(
                        'file' => 'C:\\wamp64\\www\\personal\\watchtower\\tests\\script\\functional_test.php',
                        'line' => 61,
                        'function' => 'triggerError',
                        'class' => 'WatchTower\\Tests\\Core\\EventLauncher',
                        'type' => '->',
                        'args' =>
                            array(
                                0 => 2,
                                1 => 'E_WARNING triggered',
                            ),
                    ),
            ),
    ];

    /**
     * @return ErrorEvent
     */
    public function test__construct()
    {
        $e = new ErrorEvent($this->infoArr);
        $this->assertInstanceOf(ErrorEvent::class, $e);
        return $e;
    }

    /**
     * @depends test__construct
     * @param ErrorEvent $e
     */
    public function testGetMessage(ErrorEvent $e)
    {
        $this->assertIsString($e->getMessage());
        $this->assertNotEmpty($e->getMessage());
    }


    /**
     * @depends test__construct
     * @param ErrorEvent $e
     */
    public function testGetName(ErrorEvent $e)
    {
        $this->assertIsString($e->getName());
        $this->assertNotEmpty($e->getName());
    }

    /**
     * @depends test__construct
     * @param ErrorEvent $e
     */
    public function testGetTrace(ErrorEvent $e)
    {
        $this->assertIsArray($e->getTrace());
        $this->assertNotEmpty($e->getTrace());
    }

    /**
     * @depends test__construct
     * @param ErrorEvent $e
     */
    public function testGetLine(ErrorEvent $e)
    {
        $this->assertNotEmpty($e->getLine());
        $this->assertIsInt($e->getLine());
    }

    /**
     * @depends test__construct
     * @param ErrorEvent $e
     */
    public function testGetTraceAsString(ErrorEvent $e)
    {
        $this->assertIsString($e->getTraceAsString());
        $this->assertNotEmpty($e->getTraceAsString());
    }

    /**
     * @depends test__construct
     * @param ErrorEvent $e
     */
    public function testGetFile(ErrorEvent $e)
    {
        $this->assertIsString($e->getFile());
        $this->assertNotEmpty($e->getFile());
    }

    /**
     * @depends test__construct
     * @param ErrorEvent $e
     */
    public function testGetType(ErrorEvent $e)
    {
        $this->assertEquals('error',$e->getType());
    }

    /**
     * @depends test__construct
     * @param ErrorEvent $e
     */
    public function testGetErrorInfo(ErrorEvent $e)
    {
        $this->assertIsArray($e->getErrorInfo());
        $this->assertNotEmpty($e->getErrorInfo());
        $this->assertArrayHasKey('message',$e->getErrorInfo());
        $this->assertArrayHasKey('code',$e->getErrorInfo());
        $this->assertArrayHasKey('file',$e->getErrorInfo());
        $this->assertArrayHasKey('line',$e->getErrorInfo());
        $this->assertArrayHasKey('trace',$e->getErrorInfo());
    }


    /**
     * @depends test__construct
     * @param ErrorEvent $e
     */
    public function testGetCode(ErrorEvent $e)
    {
        $this->assertIsInt($e->getCode());
        $this->assertNotEmpty($e->getCode());
    }

    /**
     * @depends test__construct
     * @param ErrorEvent $e
     */
    public function testGetException(ErrorEvent $e)
    {
        $this->assertInstanceOf(\Throwable::class,$e->getException());
    }

    /**
     * @depends test__construct
     * @param ErrorEvent $e
     */
    public function testIsCategoryMatch(ErrorEvent $e)
    {
        //E_WARNING
        $this->assertTrue($e->isCategoryMatch((E_ERROR | E_WARNING)));
        $this->assertFalse($e->isCategoryMatch((E_ERROR)));
        $this->assertFalse($e->isCategoryMatch((E_NOTICE)));
        $this->assertTrue($e->isCategoryMatch((E_ALL)));
    }
}

<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use WatchTower\Events\ExceptionEvent;

class ExceptionEventTest extends TestCase
{

    /**
     * @return ExceptionEvent
     */
    public function test__construct()
    {
        $e = new ExceptionEvent(new \ErrorException('Testing error exception',1,1));
        $this->assertInstanceOf(ExceptionEvent::class, $e);
        return $e;
    }


    /**
     * @depends test__construct
     * @param ExceptionEvent $e
     */
    public function testGetMessage(ExceptionEvent $e)
    {
        $this->assertIsString($e->getMessage());
        $this->assertNotEmpty($e->getMessage());
    }


    /**
     * @depends test__construct
     * @param ExceptionEvent $e
     */
    public function testGetName(ExceptionEvent $e)
    {
        $this->assertIsString($e->getName());
        $this->assertNotEmpty($e->getName());
    }

    /**
     * @depends test__construct
     * @param ExceptionEvent $e
     */
    public function testGetTrace(ExceptionEvent $e)
    {
        $this->assertIsArray($e->getTrace());
        $this->assertNotEmpty($e->getTrace());
    }

    /**
     * @depends test__construct
     * @param ExceptionEvent $e
     */
    public function testGetLine(ExceptionEvent $e)
    {
        $this->assertNotEmpty($e->getLine());
        $this->assertIsInt($e->getLine());
    }

    /**
     * @depends test__construct
     * @param ExceptionEvent $e
     */
    public function testGetTraceAsString(ExceptionEvent $e)
    {
        $this->assertIsString($e->getTraceAsString());
        $this->assertNotEmpty($e->getTraceAsString());
    }

    /**
     * @depends test__construct
     * @param ExceptionEvent $e
     */
    public function testGetFile(ExceptionEvent $e)
    {
        $this->assertIsString($e->getFile());
        $this->assertNotEmpty($e->getFile());
    }

    /**
     * @depends test__construct
     * @param ExceptionEvent $e
     */
    public function testGetType(ExceptionEvent $e)
    {
        $this->assertEquals('exception',$e->getType());
    }

    /**
     * @depends test__construct
     * @param ExceptionEvent $e
     */
    public function testGetException(ExceptionEvent $e)
    {
        $exc = $e->getException();
        $this->assertIsObject($exc);
        $this->assertInstanceOf(\Throwable::class,$exc);
    }

    /**
     * @depends test__construct
     * @param ExceptionEvent $e
     */
    public function testGetCode(ExceptionEvent $e)
    {
        $this->assertIsInt($e->getCode());
        $this->assertGreaterThan(0,$e->getCode());
    }

    /**
     * @param ExceptionEvent $e
     */
    public function testIsCategoryMatch()
    {
        $e = new ExceptionEvent(new \ErrorException('Testing error exception',1,1));
        $this->assertTrue($e->isCategoryMatch(\Throwable::class));
        $this->assertTrue($e->isCategoryMatch(\Exception::class));
        $this->assertFalse($e->isCategoryMatch(\Error::class));

        $e = new ExceptionEvent(new \ArithmeticError('Testing arithmetic error exception',2));
        $this->assertTrue($e->isCategoryMatch(\ArithmeticError::class));
        $this->assertFalse($e->isCategoryMatch(\CompileError::class));
        $this->assertFalse($e->isCategoryMatch(\DivisionByZeroError::class));

        $e = new ExceptionEvent(new \DivisionByZeroError('Testing division by zero exception',3));
        $this->assertTrue($e->isCategoryMatch(\DivisionByZeroError::class));
        $this->assertTrue($e->isCategoryMatch(\ArithmeticError::class));
        $this->assertTrue($e->isCategoryMatch(\Error::class));
        $this->assertTrue($e->isCategoryMatch(\Throwable::class));
        $this->assertFalse($e->isCategoryMatch(\TypeError::class));

    }
}

<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use WatchTower\EventBuffer;
use WatchTower\Events\ErrorEvent;
use WatchTower\Exceptions\WatchTowerException;

class EventBufferTest extends TestCase
{
    /**
     * @return EventBuffer
     * @throws WatchTowerException
     */
    public function test__construct()
    {
        $eb = new EventBuffer();
        $this->assertInstanceOf(EventBuffer::class, $eb);
        return $eb;
    }

    /**
     * @depends test__construct
     * @param EventBuffer $eb
     * @throws ReflectionException
     * @throws WatchTowerException
     */
    public function testCanPush(EventBuffer $eb)
    {
        $reflection = new ReflectionClass($eb);
        $buffer = $reflection->getProperty('buffer');
        $buffer->setAccessible(true);
        $maxSize = $reflection->getProperty('maxBufferSize');
        $maxSize->setAccessible(true);
        $maxSize->setValue($eb,2);
        $errorInfo = [
            'code' => 512,
            'message' => 'Triggered parse error',
            'file' => 'C:\wamp64\www\personal\watchtower\tests\custom\functional_test.php',
            'line' => 55
        ];

        $canPush = $eb->canPush('error',$errorInfo);
        $this->assertTrue($canPush);
        $buffer->setValue($eb, ['61f4a112566e51959adc7fa392cd10f6' => (new ErrorEvent($errorInfo))]);
        $canPush = $eb->canPush('error',$errorInfo);

        $this->assertFalse($canPush);
        $errorInfo2 = [
            'code' => 512,
            'message' => 'Triggered parse error',
            'file' => 'C:\wamp64\www\personal\watchtower\tests\custom\functional_test.php',
            'line' => 56
        ];
        $e = new ErrorEvent($errorInfo2);
        $eb->push($e);
        $errorInfo3 = [
            'code' => 512,
            'message' => 'Triggered parse error',
            'file' => 'C:\wamp64\www\personal\watchtower\tests\custom\functional_test.php',
            'line' => 57
        ];
        $canPush = $eb->canPush('error',$errorInfo3);
        $this->assertFalse($canPush);


    }

    /**
     * @throws WatchTowerException
     */
    public function testPush()
    {
        $eb = EventBuffer::create();
        $errorInfo = [
            'code' => 512,
            'message' => 'Triggered parse error',
            'file' => 'C:\wamp64\www\personal\watchtower\tests\custom\functional_test.php',
            'line' => 55
        ];
        $e = new ErrorEvent($errorInfo);
        $eb->push($e);
        $c = $eb->count();
        $this->assertEquals(1,$c);
        $errorInfo2 = [
            'code' => 512,
            'message' => 'Triggered parse error',
            'file' => 'C:\wamp64\www\personal\watchtower\tests\custom\functional_test.php',
            'line' => 58
        ];
        $e2 = new ErrorEvent($errorInfo2);
        $eb->push($e2);
        $c = $eb->count();
        $this->assertEquals(2,$c);
    }

}

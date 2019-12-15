<?php

namespace WatchTower\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionException;
use WatchTower\Events\ExceptionEvent;
use WatchTower\Exceptions\WatchTowerException;
use WatchTower\Outputs\Email;
use WatchTower\Outputs\OutputTargetInterface;
use WatchTower\WatchTower;
use WatchTower\Wrappers\PHPMailerMailTransport;

class EmailTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
        WatchTower::create([]);
    }

    public function getSampleOutputStack()
    {
        return [
            'handler' => [
                'main' => '',
                'minibox' => '',
                'plaintext' => '
                     ErrorException: Undefined variable: a in file C:\a\b\c\include.php on line 8
                     Stack trace:
                      1. ErrorException->() C:\a\b\c\include.php:8
                      2. WatchTower\WatchTower->WatchTower\{closure}() C:\a\b\c\include.php:8
                      3. include_once()  C:\a\b\c\include\functional_test.php:10'
            ],
            'targets' => [
                'browser' => '',
                'file' => ['accessLink' => 'www.example.com/?file=abc']
            ]
        ];
    }

    /**
     * @return array $confMail
     */
    protected function getMailConfig()
    {
        $transport = $this->createMock(PHPMailerMailTransport::class);
        $transport
            ->method('send')->willReturn(true);
        $transport->method('getErrorMessage')->willReturn('nothing');
        $confMail = [
            'email.transport' => $transport,
            'email.recipients' => ['glisglis.14846463464894@somehost48.com'],
            'email.sender' => 'sender548648616@somehost48.com',
            'email.subject' => 'Watchtower: system error'
        ];
        return $confMail;
    }

    /**
     * @throws WatchTowerException
     */
    public function test__wrongConfig()
    {
        $this->expectException(WatchTowerException::class);
        $this->expectExceptionMessage('The config variables');
        $this->expectExceptionMessage('are missing');
        new Email();
    }

    /**
     * @throws WatchTowerException
     */
    public function test__construct()
    {
        $o = new Email($this->getMailConfig());
        $this->assertInstanceOf(Email::class, $o);
    }

    /**
     * @return Email|OutputTargetInterface
     * @throws WatchTowerException
     */
    public function testCreate()
    {
        $o = Email::create($this->getMailConfig());
        $this->assertInstanceOf(Email::class, $o);
        return $o;
    }

    /**
     * @depends testCreate
     * @param Email $o
     */
    public function testGetName(Email $o)
    {
        $name = $o->getName();
        $this->assertIsString($name);
        $this->assertNotEmpty($name);
    }

    /**
     * @depends testCreate
     * @param Email $o
     */
    public function testBuildEmailBody(Email $o)
    {
        $outputStack = $this->getSampleOutputStack();
        $body = $o->buildEmailBody($outputStack);
        $this->assertStringContainsString('ErrorException: Undefined variable', $body);
        $this->assertStringContainsString('<a href=\'www.example.com/?file=abc\'', $body);
    }

    /**
     * @depends testCreate
     * @param Email $o
     * @return Email
     * @throws ReflectionException
     * @throws WatchTowerException
     */
    public function testExecute(Email $o)
    {
        $exception = new \ErrorException('Testing message', 1, 1);
        $e = new ExceptionEvent($exception);
        $o->execute($e, 'Output this string in Email', $this->getSampleOutputStack());
        $out = $o->getOutput();
        $this->assertIsArray($out);
        $this->assertArrayHasKey('success', $out);
        $this->assertArrayHasKey('errorMsg', $out);
        $this->assertTrue($out['success']);
        $this->assertEmpty($out['errorMsg']);
        return $o;
    }
}

<?php

namespace WatchTower\Tests;

use PHPUnit\Framework\TestCase;
use WatchTower\Events\ExceptionEvent;
use WatchTower\Exceptions\WatchTowerException;
use WatchTower\Outputs\Email;
use WatchTower\Outputs\OutputTargetInterface;
use WatchTower\Wrappers\PHPMailerMailTransport;

class EmailTest extends TestCase
{

    protected function getMailConfig() {
        $transport = $this->createMock(PHPMailerMailTransport::class);
        $transport
            ->method('send')->willReturn(true);
        $transport->method('getErrorMessage')->willReturn('nothing');
        $confMail = [
            'transport'     => $transport,
            'recipients'    => ['glisglis.14846463464894@somehost48.com'],
            'sender'        => 'sender548648616@somehost48.com'
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
        $o = new Email();
    }

    /**
     * @throws WatchTowerException
     */
    public function test__construct()
    {
        $o = new Email($this->getMailConfig());
        $this->assertInstanceOf(Email::class,$o);
    }
    /**
     * @return Email|OutputTargetInterface
     */
    public function testCreate()
    {
        $o = Email::create($this->getMailConfig());
        $this->assertInstanceOf(Email::class,$o);
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
    public function testBuildEmailBody(Email $o) {
        $body = $o->buildEmailBody('e-mail body content created by handler',[
            'plaintext' => 'This will be displayed before the link to error/exception file',
            'fileAccessLink' => 'www.example.com/exc=123'
        ]);
        $this->assertStringContainsString('This will be displayed before the link to error/exception file',$body);
        $this->assertStringContainsString('<a href=\'www.example.com/exc=123\'',$body);
        $body = $o->buildEmailBody('e-mail body content created by handler',[
            'plaintext' => 'This will be displayed before the link to error/exception file',
            'fileAccessLink' => ''
        ]);
        $this->assertStringContainsString('e-mail body content created by handler',$body);

    }

    /**
     * @depends testCreate
     * @param Email $o
     * @return Email
     * @throws WatchTowerException
     */
    public function testExecute(Email $o)
    {
        $exception = new \ErrorException('Testing message',1,1);
        $e = new ExceptionEvent($exception);
        $o->execute($e,'Output this string in Email',['plaintext'=>'Output plaintext']);
        $ov = $o->getOutputVars();
        $this->assertIsArray($ov);
        $this->assertArrayHasKey('success',$ov);
        $this->assertArrayHasKey('errorMsg',$ov);
        $this->assertTrue($ov['success']);
        $this->assertEmpty($ov['errorMsg']);
        return $o;
    }

    /**
     * @depends testCreate
     * @param Email $o
     */
    public function testGetDefaultConfig(Email $o)
    {
        $dc = $o->getDefaultConfig();
        $this->assertIsArray($dc);
        $this->assertNotEmpty($dc);
        $keys = ['transport','sender','recipients','subject'];
        foreach($keys as $key) {
            $this->assertArrayHasKey($key,$dc);
        }
        $this->assertIsArray($dc['recipients']);
    }
    /**
     * @depends testExecute
     * @param Email $o
     */
    public function testGetOutputVars(Email $o)
    {
        $ov = $o->getOutputVars();
        $this->assertIsArray($ov);
        $this->assertNotEmpty($ov);
        $this->assertTrue($ov['success']);
        $this->assertEmpty($ov['errorMsg']);
    }
}

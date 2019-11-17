<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use WatchTower\Events\EventTrait;

class EventTraitTest extends TestCase
{

    public function testGetFriendlyErrorType()
    {
        /** @var EventTrait $mock */
        $mock = $this->getMockForTrait(EventTrait::class);
        $this->assertSame('E_ERROR',$mock->getFriendlyErrorType(E_ERROR));
        $this->assertSame('E_WARNING',$mock->getFriendlyErrorType(E_WARNING));
        $this->assertSame('E_NOTICE',$mock->getFriendlyErrorType(E_NOTICE));
    }
}

<?php
declare(strict_types = 1);
namespace Slothsoft\Amber;

use PHPUnit\Framework\TestCase;
use AssertionError;

/**
 * @covers SavegameController
 */
final class DataTest extends TestCase
{

    public function testData()
    {
        $this->expectException(AssertionError::class);
        
        $this->assertInstanceOf(SavegameController::class, new SavegameController(''));
    }
}

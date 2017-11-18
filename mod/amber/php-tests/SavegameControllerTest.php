<?php
declare(strict_types = 1);
namespace Slothsoft\Amber;

use PHPUnit\Framework\TestCase;
use AssertionError;

require __DIR__ . '/../../../constants.php';

/**
 * @covers Slothsoft\Amber\SavegameController
 */
final class SavegameControllerTest extends TestCase
{

    public function testData()
    {
        $this->expectException(AssertionError::class);
        
        $this->assertInstanceOf(ModController::class, new ModController(''));
    }
}

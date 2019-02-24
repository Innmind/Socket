<?php
declare(strict_types = 1);

namespace Tests\Innmind\Socket\Loop\Strategy;

use Innmind\Socket\Loop\{
    Strategy\Iterations,
    Strategy,
};
use PHPUnit\Framework\TestCase;

class IterationsTest extends TestCase
{
    public function testInterface()
    {
        $strategy = new Iterations(2);

        $this->assertInstanceOf(Strategy::class, $strategy);
        $this->assertTrue($strategy());
        $this->assertTrue($strategy());
        $this->assertFalse($strategy());
        $this->assertFalse($strategy());
    }
}

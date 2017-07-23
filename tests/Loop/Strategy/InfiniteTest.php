<?php
declare(strict_types = 1);

namespace Tests\Innmind\Socket\Loop\Strategy;

use Innmind\Socket\Loop\{
    Strategy\Infinite,
    Strategy
};
use PHPUnit\Framework\TestCase;

class InfiniteTest extends TestCase
{
    public function testInterface()
    {
        $strategy = new Infinite;

        $this->assertInstanceOf(Strategy::class, $strategy);
        $this->assertTrue($strategy());
    }
}

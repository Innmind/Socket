<?php
declare(strict_types = 1);

namespace Tests\Innmind\Socket\Exception;

use Innmind\Socket\Exception\TransportNotSupportedByTheSystem;
use PHPUnit\Framework\TestCase;

class TransportNotSupportedByTheSystemTest extends TestCase
{
    public function testConstruct()
    {
        $e = new TransportNotSupportedByTheSystem('foo', 'bar', 'baz');

        $this->assertSame('foo not supported (allowed: bar, baz)', $e->getMessage());
    }
}

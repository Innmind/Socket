<?php
declare(strict_types = 1);

namespace Tests\Innmind\Socket\Internet;

use Innmind\Socket\Internet\Transport;
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class TransportTest extends TestCase
{
    /**
     * @dataProvider cases
     */
    public function testInterface($expected, $method)
    {
        $transport = Transport::$method();

        $this->assertInstanceOf(Transport::class, $transport);
        $this->assertSame($expected, $transport->toString());
        $this->assertInstanceOf(Map::class, $transport->options());
        $this->assertSame('string', $transport->options()->keyType());
        $this->assertSame('scalar|array', $transport->options()->valueType());
        $this->assertCount(0, $transport->options());
    }

    public function testOptions()
    {
        $transport = Transport::ssl();
        $transport2 = $transport->withOption('foo', 42);

        $this->assertInstanceOf(Transport::class, $transport2);
        $this->assertNotSame($transport, $transport2);
        $this->assertSame('ssl', $transport->toString());
        $this->assertSame('ssl', $transport2->toString());
        $this->assertCount(0, $transport->options());
        $this->assertCount(1, $transport2->options());
        $this->assertSame(42, $transport2->options()->get('foo'));
    }

    public function cases(): array
    {
        return [
            ['tcp', 'tcp'],
            ['ssl', 'ssl'],
            ['ssl', 'ssl'],
            ['tls', 'tls'],
            ['tlsv1.0', 'tlsv10'],
            ['tlsv1.1', 'tlsv11'],
            ['tlsv1.2', 'tlsv12'],
        ];
    }
}

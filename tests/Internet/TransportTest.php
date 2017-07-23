<?php
declare(strict_types = 1);

namespace Tests\Innmind\Socket\Internet;

use Innmind\Socket\Internet\Transport;
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
        $this->assertSame($expected, (string) $transport);
    }

    public function cases(): array
    {
        return [
            ['tcp', 'tcp'],
            ['ssl', 'ssl'],
            ['sslv3', 'sslv3'],
            ['tls', 'tls'],
            ['tlsv1.0', 'tlsv10'],
            ['tlsv1.1', 'tlsv11'],
            ['tlsv1.2', 'tlsv12'],
        ];
    }
}

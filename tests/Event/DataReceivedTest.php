<?php
declare(strict_types = 1);

namespace Tests\Innmind\Socket\Event;

use Innmind\Socket\{
    Event\DataReceived,
    Server\Connection
};
use Innmind\Immutable\Str;
use PHPUnit\Framework\TestCase;

class DataReceivedTest extends TestCase
{
    public function testInterface()
    {
        $event = new DataReceived(
            $connection = $this->createMock(Connection::class),
            $data = new Str('')
        );

        $this->assertSame($connection, $event->source());
        $this->assertSame($data, $event->data());
    }
}

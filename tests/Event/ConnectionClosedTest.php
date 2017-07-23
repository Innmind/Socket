<?php
declare(strict_types = 1);

namespace Tests\Innmind\Socket\Event;

use Innmind\Socket\{
    Event\ConnectionClosed,
    Server\Connection
};
use PHPUnit\Framework\TestCase;

class ConnectionClosedTest extends TestCase
{
    public function testInterface()
    {
        $event = new ConnectionClosed(
            $connection = $this->createMock(Connection::class)
        );

        $this->assertSame($connection, $event->connection());
    }
}

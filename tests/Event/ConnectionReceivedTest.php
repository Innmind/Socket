<?php
declare(strict_types = 1);

namespace Tests\Innmind\Socket\Event;

use Innmind\Socket\{
    Event\ConnectionReceived,
    Server\Connection
};
use PHPUnit\Framework\TestCase;

class ConnectionReceivedTest extends TestCase
{
    public function testInterface()
    {
        $event = new ConnectionReceived(
            $connection = $this->createMock(Connection::class)
        );

        $this->assertSame($connection, $event->connection());
    }
}

<?php
declare(strict_types = 1);

namespace Tests\Innmind\Socket\Event;

use Innmind\Socket\{
    Event\ConnectionReady,
    Server\Connection,
};
use PHPUnit\Framework\TestCase;

class ConnectionReadyTest extends TestCase
{
    public function testInterface()
    {
        $event = new ConnectionReady(
            $connection = $this->createMock(Connection::class)
        );

        $this->assertSame($connection, $event->connection());
    }
}

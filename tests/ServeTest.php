<?php
declare(strict_types = 1);

namespace Tests\Innmind\Socket;

use Innmind\Socket\{
    Serve,
    Server,
    Server\Connection,
    Loop\Strategy\Iterations,
    Event\ConnectionReceived,
    Event\ConnectionReady,
    Event\ConnectionClosed,
};
use Innmind\Stream\{
    Watch,
    Watch\Ready,
    Selectable,
};
use Innmind\EventBus\EventBus;
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class ServeTest extends TestCase
{
    public function testInvokation()
    {
        $serve = new Serve(
            $bus = $this->createMock(EventBus::class),
            $watch = $this->createMock(Watch::class),
            new Iterations(3)
        );
        $server = $this->createMock(Server::class);
        $watch
            ->expects($this->once())
            ->method('forRead')
            ->with($server)
            ->willReturn($watch2 = $this->createMock(Watch::class));
        $watch2
            ->expects($this->at(0))
            ->method('__invoke')
            ->willReturn(new Ready(
                Set::of(Selectable::class, $server),
                Set::of(Selectable::class),
                Set::of(Selectable::class)
            ));
        $server
            ->expects($this->once())
            ->method('accept')
            ->willReturn($connection = $this->createMock(Connection::class));
        $watch2
            ->expects($this->at(1))
            ->method('forRead')
            ->with($connection)
            ->willReturn($watch3 = $this->createMock(Watch::class));
        $bus
            ->expects($this->at(0))
            ->method('__invoke')
            ->with($this->callback(function(ConnectionReceived $e) use ($connection): bool {
                return $e->connection() === $connection;
            }));
        $watch3
            ->expects($this->at(0))
            ->method('__invoke')
            ->willReturn(new Ready(
                Set::of(Selectable::class, $connection),
                Set::of(Selectable::class),
                Set::of(Selectable::class)
            ));
        $connection
            ->expects($this->at(0))
            ->method('closed')
            ->willReturn(false);
        $bus
            ->expects($this->at(1))
            ->method('__invoke')
            ->with($this->callback(function(ConnectionReady $e) use ($connection): bool {
                return $e->connection() === $connection;
            }));
        $watch3
            ->expects($this->at(1))
            ->method('__invoke')
            ->willReturn(new Ready(
                Set::of(Selectable::class, $connection),
                Set::of(Selectable::class),
                Set::of(Selectable::class)
            ));
        $connection
            ->expects($this->at(1))
            ->method('closed')
            ->willReturn(false);
        $bus
            ->expects($this->at(2))
            ->method('__invoke')
            ->with($this->callback(function(ConnectionReady $e) use ($connection): bool {
                return $e->connection() === $connection;
            }))
            ->will($this->throwException($exception = new \Exception));
        $bus
            ->expects($this->at(3))
            ->method('__invoke')
            ->with($exception);
        $watch3
            ->expects($this->at(2))
            ->method('__invoke')
            ->willReturn(new Ready(
                Set::of(Selectable::class, $connection),
                Set::of(Selectable::class),
                Set::of(Selectable::class)
            ));
        $connection
            ->expects($this->at(2))
            ->method('closed')
            ->willReturn(true);
        $bus
            ->expects($this->at(4))
            ->method('__invoke')
            ->with($this->callback(function(ConnectionClosed $e) use ($connection): bool {
                return $e->connection() === $connection;
            }));
        $watch3
            ->expects($this->at(3))
            ->method('unwatch')
            ->with($connection)
            ->willReturn($watch4 = $this->createMock(Watch::class));

        $this->assertNull($serve($server));
    }
}

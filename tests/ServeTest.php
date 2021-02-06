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
        $server
            ->expects($this->once())
            ->method('accept')
            ->willReturn($connection = $this->createMock(Connection::class));
        $connection
            ->expects($this->exactly(3))
            ->method('closed')
            ->will($this->onConsecutiveCalls(false, false, true));
        $exception = new \Exception;
        $bus
            ->expects($this->exactly(5))
            ->method('__invoke')
            ->withConsecutive(
                [$this->callback(static function(ConnectionReceived $e) use ($connection): bool {
                    return $e->connection() === $connection;
                })],
                [$this->callback(static function(ConnectionReady $e) use ($connection): bool {
                    return $e->connection() === $connection;
                })],
                [$this->callback(static function(ConnectionReady $e) use ($connection): bool {
                    return $e->connection() === $connection;
                })],
                [$exception],
                [$this->callback(static function(ConnectionClosed $e) use ($connection): bool {
                    return $e->connection() === $connection;
                })],
            )
            ->will($this->onConsecutiveCalls(
                null,
                null,
                $this->throwException($exception),
                null,
                null,
            ));
        $watch
            ->expects($this->once())
            ->method('forRead')
            ->with($server)
            ->willReturn($watch2 = $this->createMock(Watch::class));
        $watch2
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn(new Ready(
                Set::of(Selectable::class, $server),
                Set::of(Selectable::class),
                Set::of(Selectable::class)
            ));
        $watch2
            ->expects($this->once())
            ->method('forRead')
            ->with($connection)
            ->willReturn($watch3 = $this->createMock(Watch::class));
        $watch3
            ->expects($this->exactly(3))
            ->method('__invoke')
            ->will($this->onConsecutiveCalls(
                new Ready(
                    Set::of(Selectable::class, $connection),
                    Set::of(Selectable::class),
                    Set::of(Selectable::class)
                ),
                new Ready(
                    Set::of(Selectable::class, $connection),
                    Set::of(Selectable::class),
                    Set::of(Selectable::class)
                ),
                new Ready(
                    Set::of(Selectable::class, $connection),
                    Set::of(Selectable::class),
                    Set::of(Selectable::class)
                ),
            ));
        $watch3
            ->expects($this->once())
            ->method('unwatch')
            ->with($connection)
            ->willReturn($this->createMock(Watch::class));

        $this->assertNull($serve($server));
    }
}

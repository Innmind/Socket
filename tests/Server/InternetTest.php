<?php
declare(strict_types = 1);

namespace Tests\Innmind\Socket\Server;

use Innmind\Socket\{
    Server\Internet,
    Server,
    Server\Connection,
    Internet\Transport,
};
use Innmind\Stream\{
    Stream\Position,
    PositionNotSeekable,
};
use Innmind\IP\IPv4;
use Innmind\Url\Authority\Port;
use Symfony\Component\Process\Process;
use PHPUnit\Framework\TestCase;

class InternetTest extends TestCase
{
    private $server;

    public function setUp(): void
    {
        $this->server = Internet::of(
            Transport::tcp(),
            IPv4::of('127.0.0.1'),
            Port::of(1234)
        )->match(
            static fn($server) => $server,
            static fn() => null,
        );
    }

    public function tearDown(): void
    {
        $this->server->close();
    }

    public function testInterface()
    {
        $this->assertInstanceOf(Server::class, $this->server);
    }

    public function testAccept()
    {
        $process = new Process(['php', 'fixtures/tcpClient.php']);
        $process->run();

        $this->assertInstanceOf(Connection::class, $this->server->accept()->match(
            static fn($connection) => $connection,
            static fn() => null,
        ));
    }

    public function testResource()
    {
        $this->assertIsResource($this->server->resource());
        $this->assertSame('stream', \get_resource_type($this->server->resource()));
    }

    public function testClose()
    {
        $this->assertFalse($this->server->closed());
        $this->assertNull($this->server->close()->match(
            static fn() => null,
            static fn($e) => $e,
        ));
        $this->assertTrue($this->server->closed());
    }

    public function testPosition()
    {
        $this->assertInstanceOf(Position::class, $this->server->position());
        $this->assertSame(0, $this->server->position()->toInt());
    }

    public function testReturnErrorWhenSeeking()
    {
        $this->assertInstanceOf(
            PositionNotSeekable::class,
            $this->server->seek(new Position(0))->match(
                static fn() => null,
                static fn($e) => $e,
            ),
        );
    }

    public function testReturnErrorWhenRewinding()
    {
        $this->assertInstanceOf(
            PositionNotSeekable::class,
            $this->server->rewind()->match(
                static fn() => null,
                static fn($e) => $e,
            ),
        );
    }

    public function testEnd()
    {
        $this->assertFalse($this->server->end());
    }

    public function testSize()
    {
        $this->assertFalse($this->server->size()->match(
            static fn() => true,
            static fn() => false,
        ));
    }
}

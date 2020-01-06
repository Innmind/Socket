<?php
declare(strict_types = 1);

namespace Tests\Innmind\Socket\Server;

use Innmind\Socket\{
    Server\Internet,
    Server,
    Server\Connection,
    Internet\Transport,
    Exception\SocketNotSeekable,
};
use Innmind\Stream\{
    Stream\Position,
    Exception\UnknownSize
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
        $this->server = new Internet(
            Transport::tcp(),
            IPv4::of('127.0.0.1'),
            Port::of(1234)
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

        $this->assertInstanceOf(Connection::class, $this->server->accept());
    }

    public function testResource()
    {
        $this->assertTrue(is_resource($this->server->resource()));
        $this->assertSame('stream', get_resource_type($this->server->resource()));
    }

    public function testClose()
    {
        $this->assertFalse($this->server->closed());
        $this->assertNull($this->server->close());
        $this->assertTrue($this->server->closed());
    }

    public function testPosition()
    {
        $this->assertInstanceOf(Position::class, $this->server->position());
        $this->assertSame(0, $this->server->position()->toInt());
    }

    public function testThrowWhenSeeking()
    {
        $this->expectException(SocketNotSeekable::class);

        $this->server->seek(new Position(0));
    }

    public function testThrowWhenRewinding()
    {
        $this->expectException(SocketNotSeekable::class);

        $this->server->rewind();
    }

    public function testEnd()
    {
        $this->assertFalse($this->server->end());
    }

    public function testSize()
    {
        $this->assertFalse($this->server->knowsSize());

        try {
            $this->server->size();
            $this->fail('it should throw');
        } catch (UnknownSize $e) {
            $this->assertTrue(true);
        }
    }
}

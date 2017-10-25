<?php
declare(strict_types = 1);

namespace Tests\Innmind\Socket\Server;

use Innmind\Socket\{
    Server\Internet,
    Server,
    Server\Connection,
    Internet\Transport
};
use Innmind\Stream\{
    Stream\Position,
    Exception\UnknownSize
};
use Innmind\IP\IPv4;
use Innmind\Url\Authority\Port;
use Innmind\Server\Control\{
    ServerFactory,
    Server\Command
};
use PHPUnit\Framework\TestCase;

class InternetTest extends TestCase
{
    private $server;

    public function setUp()
    {
        $this->server = new Internet(
            Transport::tcp(),
            new IPv4('127.0.0.1'),
            new Port(1234)
        );
    }

    public function tearDown()
    {
        $this->server->close();
    }

    public function testInterface()
    {
        $this->assertInstanceOf(Server::class, $this->server);
    }

    public function testAccept()
    {
        (new ServerFactory)
            ->make()
            ->processes()
            ->execute(
                Command::foreground('php')
                    ->withArgument('fixtures/tcpClient.php')
            )
            ->wait();

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
        $this->assertSame($this->server, $this->server->close());
        $this->assertTrue($this->server->closed());
    }

    public function testPosition()
    {
        $this->assertInstanceOf(Position::class, $this->server->position());
        $this->assertSame(0, $this->server->position()->toInt());
    }

    /**
     * @expectedException Innmind\Socket\Exception\SocketNotSeekable
     */
    public function testThrowWhenSeeking()
    {
        $this->server->seek(new Position(0));
    }

    /**
     * @expectedException Innmind\Socket\Exception\SocketNotSeekable
     */
    public function testThrowWhenRewinding()
    {
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

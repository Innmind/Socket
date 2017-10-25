<?php
declare(strict_types = 1);

namespace Tests\Innmind\Socket\Server;

use Innmind\Socket\{
    Server\Unix,
    Server,
    Server\Connection,
    Address\Unix as Address
};
use Innmind\Stream\{
    Stream\Position,
    Exception\UnknownSize
};
use Innmind\Server\Control\{
    ServerFactory,
    Server\Command
};
use PHPUnit\Framework\TestCase;

class UnixTest extends TestCase
{
    public function testInterface()
    {
        @unlink('/tmp/foo.sock');
        $unix = new Unix(new Address('/tmp/foo'));

        $this->assertInstanceOf(Server::class, $unix);
    }

    public function testAccept()
    {
        @unlink('/tmp/unix.sock');
        $unix = new Unix(new Address('/tmp/unix'));
        (new ServerFactory)
            ->make()
            ->processes()
            ->execute(
                Command::foreground('php')
                    ->withArgument('fixtures/unixClient.php')
            )
            ->wait();

        $this->assertInstanceOf(Connection::class, $unix->accept());
    }

    public function testRecoverable()
    {
        $this->assertInstanceOf(Unix::class, Unix::recoverable(new Address('/tmp/foo')));
    }

    public function testRecoverableWhenSockFileExisting()
    {
        @unlink('/tmp/foo.sock');
        $socket = stream_socket_server('unix:///tmp/foo.sock');
        fclose($socket);

        $this->assertInstanceOf(Unix::class, Unix::recoverable(new Address('/tmp/foo')));
    }

    public function testResource()
    {
        $unix = Unix::recoverable(new Address('/tmp/foo'));

        $this->assertTrue(is_resource($unix->resource()));
        $this->assertSame('stream', get_resource_type($unix->resource()));
    }

    public function testClose()
    {
        $unix = Unix::recoverable(new Address('/tmp/foo'));

        $this->assertFalse($unix->closed());
        $this->assertTrue(file_exists('/tmp/foo.sock'));
        $this->assertSame($unix, $unix->close());
        $this->assertTrue($unix->closed());
        $this->assertFalse(file_exists('/tmp/foo.sock'));
    }

    public function testPosition()
    {
        $unix = Unix::recoverable(new Address('/tmp/foo'));

        $this->assertInstanceOf(Position::class, $unix->position());
        $this->assertSame(0, $unix->position()->toInt());
    }

    /**
     * @expectedException Innmind\Socket\Exception\SocketNotSeekable
     */
    public function testThrowWhenSeeking()
    {
        Unix::recoverable(new Address('/tmp/foo'))->seek(new Position(0));
    }

    /**
     * @expectedException Innmind\Socket\Exception\SocketNotSeekable
     */
    public function testThrowWhenRewinding()
    {
        Unix::recoverable(new Address('/tmp/foo'))->rewind();
    }

    public function testEnd()
    {
        $unix = Unix::recoverable(new Address('/tmp/foo'));

        $this->assertFalse($unix->end());
    }

    public function testSize()
    {
        $unix = Unix::recoverable(new Address('/tmp/foo'));

        $this->assertFalse($unix->knowsSize());

        try {
            $unix->size();
            $this->fail('it should throw');
        } catch (UnknownSize $e) {
            $this->assertTrue(true);
        }
    }
}

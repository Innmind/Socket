<?php
declare(strict_types = 1);

namespace Tests\Innmind\Socket\Server;

use Innmind\Socket\{
    Server\Unix,
    Server,
    Server\Connection,
    Address\Unix as Address,
};
use Innmind\Stream\{
    Stream\Position,
    PositionNotSeekable,
};
use Innmind\Url\Path;
use Symfony\Component\Process\Process;
use PHPUnit\Framework\TestCase;

class UnixTest extends TestCase
{
    public function testInterface()
    {
        @\unlink('/tmp/foo.sock');
        $unix = Unix::of(new Address(Path::of(\tempnam(\sys_get_temp_dir(), 'innmind'))))->match(
            static fn($socket) => $socket,
            static fn() => null,
        );

        $this->assertInstanceOf(Server::class, $unix);
    }

    public function testAccept()
    {
        @\unlink('/tmp/unix.sock');
        $unix = Unix::of(new Address(Path::of('/tmp/unix')))->match(
            static fn($socket) => $socket,
            static fn() => null,
        );
        $process = new Process(['php', 'fixtures/unixClient.php']);
        $process->run();

        $this->assertInstanceOf(Connection::class, $unix->accept()->match(
            static fn($connection) => $connection,
            static fn() => null,
        ));
    }

    public function testRecoverable()
    {
        $this->assertInstanceOf(
            Unix::class,
            Unix::recoverable(new Address(Path::of(\tempnam(\sys_get_temp_dir(), 'innmind'))))->match(
                static fn($socket) => $socket,
                static fn() => null,
            ),
        );
    }

    public function testRecoverableWhenSockFileExisting()
    {
        @\unlink('/tmp/foo.sock');
        $socket = \stream_socket_server('unix:///tmp/foo.sock');
        \fclose($socket);

        $this->assertInstanceOf(
            Unix::class,
            Unix::recoverable(new Address(Path::of(\tempnam(\sys_get_temp_dir(), 'innmind'))))->match(
                static fn($socket) => $socket,
                static fn() => null,
            ),
        );
    }

    public function testResource()
    {
        $unix = Unix::recoverable(new Address(Path::of(\tempnam(\sys_get_temp_dir(), 'innmind'))))->match(
            static fn($socket) => $socket,
            static fn() => null,
        );

        $this->assertIsResource($unix->resource());
        $this->assertSame('stream', \get_resource_type($unix->resource()));
    }

    public function testClose()
    {
        $unix = Unix::recoverable(new Address(Path::of($path = \tempnam(\sys_get_temp_dir(), 'innmind'))))->match(
            static fn($socket) => $socket,
            static fn() => null,
        );

        $this->assertFalse($unix->closed());
        $this->assertFileExists($path);
        $this->assertNull($unix->close()->match(
            static fn() => null,
            static fn($e) => $e,
        ));
        $this->assertTrue($unix->closed());
        $this->assertFileDoesNotExist($path);
    }

    public function testPosition()
    {
        $unix = Unix::recoverable(new Address(Path::of(\tempnam(\sys_get_temp_dir(), 'innmind'))))->match(
            static fn($socket) => $socket,
            static fn() => null,
        );

        $this->assertInstanceOf(Position::class, $unix->position());
        $this->assertSame(0, $unix->position()->toInt());
    }

    public function testReturnErrorWhenSeeking()
    {
        $either = Unix::recoverable(new Address(Path::of(\tempnam(\sys_get_temp_dir(), 'innmind'))))->match(
            static fn($socket) => $socket->seek(new Position(0)),
            static fn() => null,
        );

        $this->assertInstanceOf(
            PositionNotSeekable::class,
            $either->match(
                static fn() => null,
                static fn($e) => $e,
            ),
        );
    }

    public function testReturnErrorWhenRewinding()
    {
        $either = Unix::recoverable(new Address(Path::of(\tempnam(\sys_get_temp_dir(), 'innmind'))))->match(
            static fn($socket) => $socket->rewind(),
            static fn() => null,
        );

        $this->assertInstanceOf(
            PositionNotSeekable::class,
            $either->match(
                static fn() => null,
                static fn($e) => $e,
            ),
        );
    }

    public function testEnd()
    {
        $unix = Unix::recoverable(new Address(Path::of(\tempnam(\sys_get_temp_dir(), 'innmind'))))->match(
            static fn($socket) => $socket,
            static fn() => null,
        );

        $this->assertFalse($unix->end());
    }

    public function testSize()
    {
        $unix = Unix::recoverable(new Address(Path::of(\tempnam(\sys_get_temp_dir(), 'innmind'))))->match(
            static fn($socket) => $socket,
            static fn() => null,
        );

        $this->assertFalse($unix->size()->match(
            static fn() => true,
            static fn() => false,
        ));
    }
}

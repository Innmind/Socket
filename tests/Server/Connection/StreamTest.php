<?php
declare(strict_types = 1);

namespace Tests\Innmind\Socket\Server\Connection;

use Innmind\Socket\{
    Server\Connection\Stream,
    Server\Connection,
    Server\Unix,
    Address\Unix as Address,
};
use Innmind\Stream\{
    Stream\Position,
    PositionNotSeekable,
};
use Innmind\Url\Path;
use Innmind\Immutable\Str;
use PHPUnit\Framework\TestCase;

class StreamTest extends TestCase
{
    private $stream;

    public function setUp(): void
    {
        $resource = \tmpfile();
        \fwrite($resource, "foo\nbar");
        \fseek($resource, 0);

        $this->stream = new Stream($resource);
    }

    public function testInterface()
    {
        $this->assertInstanceOf(Connection::class, $this->stream);
    }

    public function testResource()
    {
        $this->assertIsResource($this->stream->resource());
        $this->assertSame('stream', \get_resource_type($this->stream->resource()));
    }

    public function testClose()
    {
        $this->assertFalse($this->stream->closed());
        $this->assertNull($this->stream->close()->match(
            static fn() => null,
            static fn($e) => $e,
        ));
        $this->assertTrue($this->stream->closed());
    }

    public function testPosition()
    {
        $this->assertInstanceOf(Position::class, $this->stream->position());
        $this->assertSame(0, $this->stream->position()->toInt());
    }

    public function testReturnErrorWhenSeeking()
    {
        $this->assertInstanceOf(
            PositionNotSeekable::class,
            $this->stream->seek(new Position(0))->match(
                static fn() => null,
                static fn($e) => $e,
            ),
        );
    }

    public function testReturnErrorWhenRewinding()
    {
        $this->assertInstanceOf(
            PositionNotSeekable::class,
            $this->stream->rewind()->match(
                static fn() => null,
                static fn($e) => $e,
            ),
        );
    }

    public function testEnd()
    {
        $this->assertFalse($this->stream->end());
    }

    public function testSize()
    {
        $this->assertFalse($this->stream->size()->match(
            static fn() => true,
            static fn() => false,
        ));
    }

    public function testRead()
    {
        $text = $this->stream->read(3)->match(
            static fn($text) => $text,
            static fn() => null,
        );
        $this->assertInstanceOf(Str::class, $text);
        $this->assertSame('foo', $text->toString());
    }

    public function testReadRemaining()
    {
        $text = $this->stream->read()->match(
            static fn($text) => $text,
            static fn() => null,
        );
        $this->assertInstanceOf(Str::class, $text);
        $this->assertSame("foo\nbar", $text->toString());
    }

    public function testReadLine()
    {
        $text = $this->stream->readLine()->match(
            static fn($text) => $text,
            static fn() => null,
        );
        $this->assertInstanceOf(Str::class, $text);
        $this->assertSame("foo\n", $text->toString());
    }

    public function testWrite()
    {
        $server = Unix::recoverable(new Address(Path::of('/tmp/foo')))->match(
            static fn($socket) => $socket,
            static fn() => null,
        );
        $client = \stream_socket_client('unix:///tmp/foo.sock');
        $stream = new Stream(\stream_socket_accept($server->resource()));

        $this->assertNull($stream->write(Str::of('baz'))->match(
            static fn() => null,
            static fn($e) => $e,
        ));
        $this->assertSame('baz', \fread($client, 3));
    }

    public function testStringCast()
    {
        $server = Unix::recoverable(new Address(Path::of('/tmp/foo')))->match(
            static fn($socket) => $socket,
            static fn() => null,
        );
        \stream_socket_client('unix:///tmp/foo.sock');
        $stream = new Stream(\stream_socket_accept($server->resource()));

        $this->assertSame('/tmp/foo.sock', $stream->toString()->match(
            static fn($text) => $text,
            static fn() => null,
        ));
    }
}

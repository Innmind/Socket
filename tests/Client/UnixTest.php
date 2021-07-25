<?php
declare(strict_types = 1);

namespace Tests\Innmind\Socket\Client;

use Innmind\Socket\{
    Client\Unix,
    Client,
    Address\Unix as Address,
    Server\Unix as Server,
    Exception\SocketNotSeekable,
};
use Innmind\Stream\Stream\Position;
use Innmind\Url\Path;
use Innmind\Immutable\Str;
use PHPUnit\Framework\TestCase;

class UnixTest extends TestCase
{
    private $client;
    private $server;

    public function setUp(): void
    {
        $this->server = Server::recoverable($address = new Address(Path::of('/tmp/foo')))->match(
            static fn($socket) => $socket,
            static fn() => null,
        );
        $this->client = Unix::of($address)->match(
            static fn($client) => $client,
            static fn() => null,
        );
    }

    public function tearDown(): void
    {
        $this->client->close();
        $this->server->close();
    }

    public function testInterface()
    {
        $this->assertInstanceOf(Client::class, $this->client);
    }

    public function testResource()
    {
        $this->assertIsResource($this->client->resource());
        $this->assertSame('stream', \get_resource_type($this->client->resource()));
    }

    public function testClose()
    {
        $this->assertFalse($this->client->closed());
        $this->assertNull($this->client->close());
        $this->assertTrue($this->client->closed());
    }

    public function testPosition()
    {
        $this->assertInstanceOf(Position::class, $this->client->position());
        $this->assertSame(0, $this->client->position()->toInt());
    }

    public function testThrowWhenSeeking()
    {
        $this->expectException(SocketNotSeekable::class);

        $this->client->seek(new Position(0));
    }

    public function testThrowWhenRewinding()
    {
        $this->expectException(SocketNotSeekable::class);

        $this->client->rewind();
    }

    public function testEnd()
    {
        $this->assertFalse($this->client->end());
    }

    public function testSize()
    {
        $this->assertFalse($this->client->size()->match(
            static fn() => true,
            static fn() => false,
        ));
    }

    public function testRead()
    {
        $this->client->write(Str::of('foobar'));
        $this->server->accept()->match(
            static fn($connection) => $connection->write(Str::of('foobar')),
            static fn() => null,
        );
        $text = $this->client->read(3);

        $this->assertInstanceOf(Str::class, $text);
        $this->assertSame('foo', $text->toString());
        $this->assertSame('bar', $this->client->read(3)->toString());
    }

    public function testReadRemaining()
    {
        $this->client->write(Str::of('foobar'));
        $this->server->accept()->match(
            static fn($connection) => $connection->write(Str::of('foobar')),
            static fn() => null,
        );
        $text = $this->client->read();

        $this->assertInstanceOf(Str::class, $text);
        $this->assertSame('foobar', $text->toString());
        $this->assertSame('', $this->client->read(3)->toString());
    }

    public function testReadLine()
    {
        $this->client->write(Str::of('foobar'));
        $this->server->accept()->match(
            static fn($connection) => $connection->write(Str::of("foo\nbar")),
            static fn() => null,
        );
        $text = $this->client->readLine();

        $this->assertInstanceOf(Str::class, $text);
        $this->assertSame("foo\n", $text->toString());
        $this->assertSame('bar', $this->client->readLine()->toString());
    }

    public function testWrite()
    {
        $this->client->write(Str::of('foobar'));
        $text = $this->server->accept()->match(
            static fn($connection) => $connection->read(),
            static fn() => null,
        );

        $this->assertInstanceOf(Str::class, $text);
        $this->assertSame('foobar', $text->toString());
    }

    public function testStringCast()
    {
        $this->assertSame('/tmp/foo.sock', $this->client->toString());
    }

    public function testClosedWhenServerConnectionClosed()
    {
        $this->assertFalse($this->client->closed());
        $connection = $this->server->accept()->match(
            static fn($connection) => $connection,
            static fn() => null,
        );
        $this->assertFalse($this->client->closed());
        $connection->close();
        $this->assertTrue($this->client->closed());
    }
}

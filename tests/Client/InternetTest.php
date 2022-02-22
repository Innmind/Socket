<?php
declare(strict_types = 1);

namespace Tests\Innmind\Socket\Client;

use Innmind\Socket\{
    Client\Internet,
    Client,
    Internet\Transport,
    Server\Internet as Server,
};
use Innmind\Stream\{
    Stream\Position,
    PositionNotSeekable,
};
use Innmind\IP\IPv4;
use Innmind\Url\{
    Url,
    Authority\Port,
};
use Innmind\Immutable\Str;
use PHPUnit\Framework\TestCase;

class InternetTest extends TestCase
{
    private $client;
    private $server;

    public function setUp(): void
    {
        $this->server = Server::of(
            Transport::tcp(),
            IPv4::of('127.0.0.1'),
            Port::of(1234),
        )->match(
            static fn($server) => $server,
            static fn() => null,
        );
        $this->client = Internet::of(
            Transport::tcp(),
            Url::of('//127.0.0.1:1234')->authority(),
        )->match(
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

    public function testClientWithOptions()
    {
        $client = Internet::of(
            Transport::tcp()->withOption('verify_host', true),
            Url::of('//127.0.0.1:1234')->authority(),
        )->match(
            static fn($client) => $client,
            static fn() => null,
        );

        $this->assertInstanceOf(Client::class, $client);
    }

    public function testResource()
    {
        $this->assertIsResource($this->client->resource());
        $this->assertSame('stream', \get_resource_type($this->client->resource()));
    }

    public function testClose()
    {
        $this->assertFalse($this->client->closed());
        $this->assertNull($this->client->close()->match(
            static fn() => null,
            static fn($e) => $e,
        ));
        $this->assertTrue($this->client->closed());
    }

    public function testPosition()
    {
        $this->assertInstanceOf(Position::class, $this->client->position());
        $this->assertSame(0, $this->client->position()->toInt());
    }

    public function testReturnErrorWhenSeeking()
    {
        $this->assertInstanceOf(
            PositionNotSeekable::class,
            $this->client->seek(new Position(0))->match(
                static fn() => null,
                static fn($e) => $e,
            ),
        );
    }

    public function testReturnErrorWhenRewinding()
    {
        $this->assertInstanceOf(
            PositionNotSeekable::class,
            $this->client->rewind()->match(
                static fn() => null,
                static fn($e) => $e,
            ),
        );
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
        $text = $this->client->read(3)->match(
            static fn($text) => $text,
            static fn() => null,
        );

        $this->assertInstanceOf(Str::class, $text);
        $this->assertSame('foo', $text->toString());
        $this->assertSame('bar', $this->client->read(3)->match(
            static fn($text) => $text->toString(),
            static fn() => null,
        ));
    }

    public function testReadRemaining()
    {
        $this->client->write(Str::of('foobar'));
        $this->server->accept()->match(
            static fn($connection) => $connection->write(Str::of('foobar')),
            static fn() => null,
        );
        $text = $this->client->read()->match(
            static fn($text) => $text,
            static fn() => null,
        );

        $this->assertInstanceOf(Str::class, $text);
        $this->assertSame('foobar', $text->toString());
        $this->assertSame('', $this->client->read(3)->match(
            static fn($remaining) => $remaining->toString(),
            static fn() => null,
        ));
    }

    public function testReadLine()
    {
        $this->client->write(Str::of('foobar'));
        $this->server->accept()->match(
            static fn($connection) => $connection->write(Str::of("foo\nbar")),
            static fn() => null,
        );
        $text = $this->client->readLine()->match(
            static fn($line) => $line,
            static fn() => null,
        );

        $this->assertInstanceOf(Str::class, $text);
        $this->assertSame("foo\n", $text->toString());
        $this->assertSame('bar', $this->client->readLine()->match(
            static fn($line) => $line->toString(),
            static fn() => null,
        ));
    }

    public function testWrite()
    {
        $this->client->write(Str::of('foobar'));
        $text = $this->server->accept()->match(
            static fn($connection) => $connection->read()->match(
                static fn($remaining) => $remaining,
                static fn() => null,
            ),
            static fn() => null,
        );

        $this->assertInstanceOf(Str::class, $text);
        $this->assertSame('foobar', $text->toString());
    }

    public function testStringCast()
    {
        $this->assertNull($this->client->toString()->match(
            static fn($address) => $address,
            static fn() => null,
        ));
    }

    public function testEndWhenServerConnectionClosed()
    {
        $this->assertFalse($this->client->closed());
        $connection = $this->server->accept()->match(
            static fn($connection) => $connection,
            static fn() => null,
        );
        $this->assertFalse($this->client->closed());
        $connection->close();
        $this->assertTrue($this->client->end());
    }
}

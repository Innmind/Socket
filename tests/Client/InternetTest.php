<?php
declare(strict_types = 1);

namespace Tests\Innmind\Socket\Client;

use Innmind\Socket\{
    Client\Internet,
    Client,
    Internet\Transport,
    Server\Internet as Server,
    Exception\SocketNotSeekable,
};
use Innmind\Stream\{
    Stream\Position,
    Exception\UnknownSize
};
use Innmind\IP\IPv4;
use Innmind\Url\{
    Url,
    Authority\Port
};
use Innmind\Immutable\Str;
use PHPUnit\Framework\TestCase;

class InternetTest extends TestCase
{
    private $client;
    private $server;

    public function setUp(): void
    {
        $this->server = new Server(
            Transport::tcp(),
            new IPv4('127.0.0.1'),
            new Port(1234)
        );
        $this->client = new Internet(
            Transport::tcp(),
            Url::fromString('//127.0.0.1:1234')->authority()
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
        $client = new Internet(
            Transport::tcp()->withOption('verify_host', true),
            Url::fromString('//127.0.0.1:1234')->authority()
        );

        $this->assertInstanceOf(Client::class, $client);
    }

    public function testResource()
    {
        $this->assertTrue(is_resource($this->client->resource()));
        $this->assertSame('stream', get_resource_type($this->client->resource()));
    }

    public function testClose()
    {
        $this->assertFalse($this->client->closed());
        $this->assertSame($this->client, $this->client->close());
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
        $this->assertFalse($this->client->knowsSize());

        try {
            $this->client->size();
            $this->fail('it should throw');
        } catch (UnknownSize $e) {
            $this->assertTrue(true);
        }
    }

    public function testRead()
    {
        $this->client->write(new Str('foobar'));
        $this->server->accept()->write(new Str('foobar'));
        $text = $this->client->read(3);

        $this->assertInstanceOf(Str::class, $text);
        $this->assertSame('foo', (string) $text);
        $this->assertSame('bar', (string) $this->client->read(3));
    }

    public function testReadRemaining()
    {
        $this->client->write(new Str('foobar'));
        $this->server->accept()->write(new Str('foobar'));
        $text = $this->client->read();

        $this->assertInstanceOf(Str::class, $text);
        $this->assertSame('foobar', (string) $text);
        $this->assertSame('', (string) $this->client->read(3));
    }

    public function testReadLine()
    {
        $this->client->write(new Str('foobar'));
        $this->server->accept()->write(new Str("foo\nbar"));
        $text = $this->client->readLine();

        $this->assertInstanceOf(Str::class, $text);
        $this->assertSame("foo\n", (string) $text);
        $this->assertSame('bar', (string) $this->client->readLine());
    }

    public function testWrite()
    {
        $this->client->write(new Str('foobar'));
        $text = $this->server->accept()->read();

        $this->assertInstanceOf(Str::class, $text);
        $this->assertSame('foobar', (string) $text);
    }

    public function testStringCast()
    {
        $this->assertSame('127.0.0.1:1234', (string) $this->client);
    }

    public function testClosedWhenServerConnectionClosed()
    {
        $this->assertFalse($this->client->closed());
        $connection = $this->server->accept();
        $this->assertFalse($this->client->closed());
        $connection->close();
        $this->assertTrue($this->client->closed());
    }
}

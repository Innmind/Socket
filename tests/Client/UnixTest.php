<?php
declare(strict_types = 1);

namespace Tests\Innmind\Socket\Client;

use Innmind\Socket\{
    Client\Unix,
    Client,
    Address\Unix as Address,
    Server\Unix as Server
};
use Innmind\Stream\{
    Stream\Position,
    Exception\UnknownSize
};
use Innmind\Immutable\Str;
use PHPUnit\Framework\TestCase;

class UnixTest extends TestCase
{
    private $client;
    private $server;

    public function setUp()
    {
        $this->server = Server::recoverable($address = new Address('/tmp/foo'));
        $this->client = new Unix($address);
    }

    public function tearDown()
    {
        unset($this->client);
        unset($this->server);
    }

    public function testInterface()
    {
        $this->assertInstanceOf(Client::class, $this->client);
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

    /**
     * @expectedException Innmind\Socket\Exception\SocketNotSeekable
     */
    public function testThrowWhenSeeking()
    {
        $this->client->seek(new Position(0));
    }

    /**
     * @expectedException Innmind\Socket\Exception\SocketNotSeekable
     */
    public function testThrowWhenRewinding()
    {
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
        $this->assertSame('/tmp/foo.sock', (string) $this->client);
    }

    public function testDestruct()
    {
        $resource = $this->client->resource();

        unset($this->client);
        $this->assertFalse(is_resource($resource));
    }
}

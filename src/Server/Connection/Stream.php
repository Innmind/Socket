<?php
declare(strict_types = 1);

namespace Innmind\Socket\Server\Connection;

use Innmind\Socket\{
    Server\Connection,
    Exception\SocketNotSeekable
};
use Innmind\Stream\{
    Stream as StreamInterface,
    Writable,
    Stream\Bidirectional,
    Stream\Position,
    Stream\Size,
    Stream\Position\Mode,
    Exception\UnknownSize
};
use Innmind\Immutable\Str;

final class Stream implements Connection
{
    private $stream;
    private $name;

    public function __construct($resource)
    {
        $this->stream = new Bidirectional($resource);
        $this->name = stream_socket_get_name($resource, false);
    }

    /**
     * {@inheritdoc}
     */
    public function resource()
    {
        return $this->stream->resource();
    }

    public function close(): StreamInterface
    {
        $this->stream->close();

        return $this;
    }

    public function closed(): bool
    {
        return $this->stream->closed();
    }

    public function position(): Position
    {
        return $this->stream->position();
    }

    public function seek(Position $position, Mode $mode = null): StreamInterface
    {
        throw new SocketNotSeekable;
    }

    public function rewind(): StreamInterface
    {
        throw new SocketNotSeekable;
    }

    public function end(): bool
    {
        return $this->stream->end();
    }

    public function size(): Size
    {
        throw new UnknownSize;
    }

    public function knowsSize(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function read(int $length = null): Str
    {
        return $this->stream->read($length);
    }

    public function readLine(): Str
    {
        return $this->stream->readLine();
    }

    public function write(Str $data): Writable
    {
        $this->stream->write($data);

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function __destruct()
    {
        $this->close();
    }
}

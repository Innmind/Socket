<?php
declare(strict_types = 1);

namespace Innmind\Socket\Server\Connection;

use Innmind\Socket\{
    Server\Connection,
    Exception\SocketNotSeekable,
};
use Innmind\Stream\{
    Stream\Bidirectional,
    Stream\Position,
    Stream\Size,
    Stream\Position\Mode,
    Exception\UnknownSize,
};
use Innmind\Immutable\Str;

final class Stream implements Connection
{
    private Bidirectional $stream;
    private string $name;

    /**
     * @param resource $resource
     */
    public function __construct($resource)
    {
        $this->stream = new Bidirectional($resource);
        $this->name = \stream_socket_get_name($resource, false) ?: '';
    }

    public function resource()
    {
        return $this->stream->resource();
    }

    public function close(): void
    {
        $this->stream->close();
    }

    public function closed(): bool
    {
        return $this->stream->closed();
    }

    public function position(): Position
    {
        return $this->stream->position();
    }

    public function seek(Position $position, Mode $mode = null): void
    {
        throw new SocketNotSeekable;
    }

    public function rewind(): void
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

    public function read(int $length = null): Str
    {
        return $this->stream->read($length);
    }

    public function readLine(): Str
    {
        return $this->stream->readLine();
    }

    public function write(Str $data): void
    {
        $this->stream->write($data);
    }

    public function toString(): string
    {
        return $this->name;
    }
}

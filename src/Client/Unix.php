<?php
declare(strict_types = 1);

namespace Innmind\Socket\Client;

use Innmind\Socket\{
    Client,
    Address\Unix as Address,
    Exception\FailedToOpenSocket,
    Exception\SocketNotSeekable
};
use Innmind\Stream\{
    Stream,
    Writable,
    Stream\Position,
    Stream\Size,
    Stream\Position\Mode,
    Exception\UnknownSize
};
use Innmind\Immutable\Str;

final class Unix implements Client
{
    private $path;
    private $stream;
    private $name;

    public function __construct(Address $path)
    {
        $this->path = (string) $path;
        $socket = @stream_socket_client('unix://'.$path);

        if ($socket === false) {
            $error = error_get_last();

            throw new FailedToOpenSocket(
                $error['message'],
                $error['type']
            );
        }

        $this->stream = new Stream\Bidirectional($socket);
        $this->name = stream_socket_get_name($socket, true);
    }

    /**
     * {@inheritdoc}
     */
    public function resource()
    {
        return $this->stream->resource();
    }

    public function close(): Stream
    {
        $this->stream->close();

        return $this;
    }

    public function closed(): bool
    {
        if ($this->stream->closed()) {
            return true;
        }

        if (feof($this->stream->resource())) {
            $this->stream->close();
        }

        return $this->stream->closed();
    }

    public function position(): Position
    {
        return $this->stream->position();
    }

    public function seek(Position $position, Mode $mode = null): Stream
    {
        throw new SocketNotSeekable;
    }

    public function rewind(): Stream
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
}

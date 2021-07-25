<?php
declare(strict_types = 1);

namespace Innmind\Socket\Client;

use Innmind\Socket\{
    Client,
    Address\Unix as Address,
    Exception\SocketNotSeekable,
};
use Innmind\Stream\{
    Stream,
    Writable,
    Stream\Position,
    Stream\Size,
    Stream\Position\Mode,
    Exception\UnknownSize,
};
use Innmind\Immutable\{
    Str,
    Maybe,
};

final class Unix implements Client
{
    private Stream\Bidirectional $stream;
    private string $name;

    /**
     * @param resource $socket
     */
    private function __construct($socket)
    {
        $this->stream = new Stream\Bidirectional($socket);
        $this->name = \stream_socket_get_name($socket, true);
    }

    /**
     * @return Maybe<self>
     */
    public static function of(Address $path): Maybe
    {
        $socket = @\stream_socket_client('unix://'.$path->toString());

        if ($socket === false) {
            /** @var Maybe<self> */
            return Maybe::nothing();
        }

        return Maybe::just(new self($socket));
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
        if ($this->stream->closed()) {
            return true;
        }

        if ($this->stream->end()) {
            $this->stream->close();
        }

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

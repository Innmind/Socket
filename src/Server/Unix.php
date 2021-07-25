<?php
declare(strict_types = 1);

namespace Innmind\Socket\Server;

use Innmind\Socket\{
    Server,
    Address\Unix as Address,
    Exception\SocketNotSeekable,
};
use Innmind\Stream\{
    Stream\Stream,
    Stream\Position,
    Stream\Size,
    Stream\Position\Mode,
    Exception\UnknownSize,
};
use Innmind\Immutable\Maybe;

final class Unix implements Server
{
    private string $path;
    /** @var resource */
    private $resource;
    private Stream $stream;

    /**
     * @param resource $socket
     */
    private function __construct(Address $path, $socket)
    {
        $this->path = $path->toString();
        $this->resource = $socket;
        $this->stream = new Stream($socket);
    }

    /**
     * @return Maybe<self>
     */
    public static function of(Address $path): Maybe
    {
        $socket = @\stream_socket_server('unix://'.$path->toString());

        if ($socket === false) {
            /** @var Maybe<self> */
            return Maybe::nothing();
        }

        return Maybe::just(new self($path, $socket));
    }

    /**
     * On open failure it will try to delete existing socket file the ntry to
     * reopen the socket connection
     *
     * @return Maybe<self>
     */
    public static function recoverable(Address $path): Maybe
    {
        return self::of($path)->otherwise(static function() use ($path) {
            @\unlink($path->toString());

            return self::of($path);
        });
    }

    public function accept(): Maybe
    {
        $socket = @\stream_socket_accept($this->resource());

        if ($socket === false) {
            /** @var Maybe<Connection> */
            return Maybe::nothing();
        }

        /** @var Maybe<Connection> */
        return Maybe::just(new Connection\Stream($socket));
    }

    public function resource()
    {
        return $this->resource;
    }

    public function close(): void
    {
        if (!$this->closed()) {
            $this->stream->close();
            @\unlink($this->path);
        }
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
}

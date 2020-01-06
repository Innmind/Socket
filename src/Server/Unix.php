<?php
declare(strict_types = 1);

namespace Innmind\Socket\Server;

use Innmind\Socket\{
    Server,
    Address\Unix as Address,
    Exception\FailedToOpenSocket,
    Exception\FailedAcceptingIncomingConnection,
    Exception\SocketNotSeekable
};
use Innmind\Stream\{
    Stream,
    Stream\Position,
    Stream\Size,
    Stream\Position\Mode,
    Exception\UnknownSize
};

final class Unix implements Server
{
    private string $path;
    /** @var resource */
    private $resource;
    private Stream\Stream $stream;

    public function __construct(Address $path)
    {
        $this->path = (string) $path;
        $socket = @stream_socket_server('unix://'.$path);

        if ($socket === false) {
            $error = error_get_last();

            throw new FailedToOpenSocket(
                $error['message'],
                $error['type']
            );
        }

        $this->resource = $socket;
        $this->stream = new Stream\Stream($socket);
    }

    /**
     * On open failure it will try to delete existing socket file the ntry to
     * reopen the socket connection
     */
    public static function recoverable(Address $path): self
    {
        try {
            return new self($path);
        } catch (FailedToOpenSocket $e) {
            @unlink((string) $path);

            return new self($path);
        }
    }

    public function accept(): Connection
    {
        $socket = @stream_socket_accept($this->resource());

        if ($socket === false) {
            $error = error_get_last();

            throw new FailedAcceptingIncomingConnection(
                $error['message'],
                $error['type']
            );
        }

        return new Connection\Stream($socket);
    }

    /**
     * {@inheritdoc}
     */
    public function resource()
    {
        return $this->resource;
    }

    public function close(): Stream
    {
        if (!$this->closed()) {
            $this->stream->close();
            @unlink($this->path);
        }

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
}

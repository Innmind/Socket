<?php
declare(strict_types = 1);

namespace Innmind\Socket\Server;

use Innmind\Socket\{
    Server,
    Internet\Transport,
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
use Innmind\IP\IP;
use Innmind\Url\Authority\PortInterface;

final class Internet implements Server
{
    private $resource;
    private $stream;

    public function __construct(
        Transport $transport,
        IP $ip,
        PortInterface $port
    ) {
        $socket = @stream_socket_server(sprintf(
            '%s://%s:%s',
            $transport,
            $ip,
            $port
        ));

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

    public function __destruct()
    {
        $this->close();
    }
}

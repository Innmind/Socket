<?php
declare(strict_types = 1);

namespace Innmind\Socket\Server;

use Innmind\Socket\{
    Server,
    Internet\Transport,
    Exception\SocketNotSeekable
};
use Innmind\Stream\{
    Stream\Stream,
    Stream\Position,
    Stream\Size,
    Stream\Position\Mode,
    Exception\UnknownSize,
};
use Innmind\IP\IP;
use Innmind\Url\Authority\Port;
use Innmind\Immutable\Maybe;

final class Internet implements Server
{
    /** @var resource */
    private $resource;
    private Stream $stream;

    /**
     * @param resource $socket
     */
    private function __construct($socket)
    {
        $this->resource = $socket;
        $this->stream = new Stream($socket);
    }

    /**
     * @return Maybe<self>
     */
    public static function of(
        Transport $transport,
        IP $ip,
        Port $port,
    ): Maybe {
        $socket = @\stream_socket_server(\sprintf(
            '%s://%s:%s',
            $transport->toString(),
            $ip->toString(),
            $port->toString(),
        ));

        if ($socket === false) {
            /** @var Maybe<self> */
            return Maybe::nothing();
        }

        return Maybe::just(new self($socket));
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
}

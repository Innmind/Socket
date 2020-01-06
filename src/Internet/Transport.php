<?php
declare(strict_types = 1);

namespace Innmind\Socket\Internet;

use Innmind\Socket\Exception\TransportNotSupportedByTheSystem;
use Innmind\Immutable\Map;

final class Transport
{
    private string $transport;
    private Map $options;

    private function __construct(string $transport)
    {
        $allowed = \stream_get_transports();

        if (!\in_array($transport, $allowed, true)) {
            throw new TransportNotSupportedByTheSystem($transport, ...$allowed);
        }

        $this->transport = $transport;
        $this->options = Map::of('string', 'variable');
    }

    public static function tcp(): self
    {
        return new self('tcp');
    }

    public static function ssl(): self
    {
        return new self('ssl');
    }

    public static function sslv3(): self
    {
        return new self('sslv3');
    }

    public static function tls(): self
    {
        return new self('tls');
    }

    public static function tlsv10(): self
    {
        return new self('tlsv1.0');
    }

    public static function tlsv11(): self
    {
        return new self('tlsv1.1');
    }

    public static function tlsv12(): self
    {
        return new self('tlsv1.2');
    }

    public function withOption(string $key, $value): self
    {
        $self = clone $this;
        $self->options = ($this->options)($key, $value);

        return $self;
    }

    /**
     * @return Map<string, variable>
     */
    public function options(): Map
    {
        return $this->options;
    }

    public function __toString(): string
    {
        return $this->transport;
    }
}

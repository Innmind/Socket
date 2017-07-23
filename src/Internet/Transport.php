<?php
declare(strict_types = 1);

namespace Innmind\Socket\Internet;

use Innmind\Socket\Exception\TransportNotSupportedByTheSystem;

final class Transport
{
    private $transport;

    private function __construct(string $transport)
    {
        $allowed = stream_get_transports();

        if (!in_array($transport, $allowed, true)) {
            throw new TransportNotSupportedByTheSystem($transport, ...$allowed);
        }

        $this->transport = $transport;
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

    public function __toString(): string
    {
        return $this->transport;
    }
}

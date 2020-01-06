<?php
declare(strict_types = 1);

namespace Innmind\Socket\Event;

use Innmind\Socket\Server\Connection;
use Innmind\Immutable\Str;

/**
 * @deprecated
 */
final class DataReceived
{
    private Connection $connection;
    private Str $data;

    public function __construct(Connection $connection, Str $data)
    {
        $this->connection = $connection;
        $this->data = $data;
    }

    public function source(): Connection
    {
        return $this->connection;
    }

    public function data(): Str
    {
        return $this->data;
    }
}

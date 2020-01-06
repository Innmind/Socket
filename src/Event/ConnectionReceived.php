<?php
declare(strict_types = 1);

namespace Innmind\Socket\Event;

use Innmind\Socket\Server\Connection;

final class ConnectionReceived
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function connection(): Connection
    {
        return $this->connection;
    }
}

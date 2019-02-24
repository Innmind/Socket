<?php
declare(strict_types = 1);

namespace Innmind\Socket\Event;

use Innmind\Socket\Server\Connection;

final class ConnectionReady
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function connection(): Connection
    {
        return $this->connection;
    }
}

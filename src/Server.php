<?php
declare(strict_types = 1);

namespace Innmind\Socket;

use Innmind\Socket\Server\Connection;
use Innmind\Stream\Selectable;
use Innmind\Immutable\Maybe;

interface Server extends Selectable
{
    /**
     * @return Maybe<Connection>
     */
    public function accept(): Maybe;
}

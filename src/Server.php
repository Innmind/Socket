<?php
declare(strict_types = 1);

namespace Innmind\Socket;

use Innmind\Socket\Server\Connection;
use Innmind\Stream\Selectable;

interface Server extends Selectable
{
    public function accept(): Connection;
}

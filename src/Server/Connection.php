<?php
declare(strict_types = 1);

namespace Innmind\Socket\Server;

use Innmind\Stream\{
    Readable,
    Writable,
    Selectable
};

interface Connection extends Readable, Writable, Selectable
{
}

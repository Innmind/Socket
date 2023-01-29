<?php
declare(strict_types = 1);

namespace Innmind\Socket\Server;

use Innmind\Stream\{
    Readable,
    Writable,
};

interface Connection extends Readable, Writable
{
}

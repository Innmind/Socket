<?php
declare(strict_types = 1);

namespace Innmind\Socket;

use Innmind\Stream\{
    Readable,
    Writable,
    Selectable,
};

interface Client extends Readable, Writable, Selectable
{
}

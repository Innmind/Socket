<?php
declare(strict_types = 1);

namespace Innmind\Socket;

use Innmind\Socket\Server\Connection;
use Innmind\Stream\{
    Readable,
    Selectable,
};
use Innmind\Immutable\Maybe;

/**
 * It only implements Readable to be usable with Stream\Watch
 *
 * Read methods are not expected to be called
 */
interface Server extends Readable, Selectable
{
    /**
     * @return Maybe<Connection>
     */
    public function accept(): Maybe;
}

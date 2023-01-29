<?php
declare(strict_types = 1);

namespace Innmind\Socket;

use Innmind\Socket\Server\Connection;
use Innmind\Stream\Readable;
use Innmind\Immutable\Maybe;

/**
 * It only implements Readable to be usable with Stream\Watch
 *
 * Read methods are not expected to be called
 */
interface Server extends Readable
{
    /**
     * @return Maybe<Connection>
     */
    public function accept(): Maybe;
}

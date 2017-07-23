<?php
declare(strict_types = 1);

namespace Innmind\Socket\Address;

use Innmind\Socket\Exception\DirectoryNotFound;

final class Unix
{
    private $path;

    public function __construct(string $path)
    {
        $parts = pathinfo($path);

        if (!is_dir($parts['dirname'])) {
            throw new DirectoryNotFound;
        }

        $this->path = sprintf(
            '%s/%s.sock',
            $parts['dirname'],
            $parts['filename']
        );
    }

    public function __toString(): string
    {
        return $this->path;
    }
}

<?php
declare(strict_types = 1);

namespace Innmind\Socket\Address;

use Innmind\Socket\Exception\DirectoryNotFound;
use Innmind\Url\Path;

final class Unix
{
    private string $path;

    public function __construct(Path $path)
    {
        /** @var array{dirname: string, filename: string} */
        $parts = pathinfo($path->toString());

        if (!is_dir($parts['dirname'])) {
            throw new DirectoryNotFound;
        }

        $this->path = sprintf(
            '%s/%s.sock',
            $parts['dirname'],
            $parts['filename'],
        );
    }

    public static function of(string $path): self
    {
        return new self(Path::of($path));
    }

    public function toString(): string
    {
        return $this->path;
    }
}

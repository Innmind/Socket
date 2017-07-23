<?php
declare(strict_types = 1);

namespace Innmind\Socket\Loop\Strategy;

use Innmind\Socket\Loop\Strategy;

final class Infinite implements Strategy
{
    public function __invoke(): bool
    {
        return true;
    }
}

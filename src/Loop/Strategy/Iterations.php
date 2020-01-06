<?php
declare(strict_types = 1);

namespace Innmind\Socket\Loop\Strategy;

use Innmind\Socket\Loop\Strategy;

final class Iterations implements Strategy
{
    private int $iterations;

    public function __construct(int $iterations)
    {
        $this->iterations = $iterations;
    }

    public function __invoke(): bool
    {
        return $this->iterations-- > 0;
    }
}

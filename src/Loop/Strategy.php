<?php
declare(strict_types = 1);

namespace Innmind\Socket\Loop;

interface Strategy
{
    public function __invoke(): bool;
}

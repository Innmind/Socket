<?php
declare(strict_types = 1);

namespace Tests\Innmind\Socket\Address;

use Innmind\Socket\{
    Address\Unix,
    Exception\DirectoryNotFound,
};
use PHPUnit\Framework\TestCase;

class UnixTest extends TestCase
{
    public function testInterface()
    {
        $this->assertSame('/tmp/foo.sock', (string) new Unix('/tmp/foo'));
        $this->assertSame('/tmp/foo.sock', (string) new Unix('/tmp/foo.so'));
        $this->assertSame('/tmp/foo.sock', (string) new Unix('/tmp/foo.sock'));
    }

    public function testThrowWhenDirectoryNotFound()
    {
        $this->expectException(DirectoryNotFound::class);

        new Unix('/whatever/file');
    }
}

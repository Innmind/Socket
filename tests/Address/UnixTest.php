<?php
declare(strict_types = 1);

namespace Tests\Innmind\Socket\Address;

use Innmind\Socket\Address\Unix;
use PHPUnit\Framework\TestCase;

class UnixTest extends TestCase
{
    public function testInterface()
    {
        $this->assertSame('/tmp/foo.sock', (string) new Unix('/tmp/foo'));
        $this->assertSame('/tmp/foo.sock', (string) new Unix('/tmp/foo.so'));
        $this->assertSame('/tmp/foo.sock', (string) new Unix('/tmp/foo.sock'));
    }

    /**
     * @expectedException Innmind\Socket\Exception\DirectoryNotFound
     */
    public function testThrowWhenDirectoryNotFound()
    {
        new Unix('/whatever/file');
    }
}

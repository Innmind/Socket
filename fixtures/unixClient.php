<?php
declare(strict_types = 1);

require 'vendor/autoload.php';

use Innmind\Socket\{
    Client\Unix,
    Address\Unix as Address
};
use Innmind\Url\Path;
use Innmind\Immutable\Str;

$socket = new Unix(new Address(Path::of('/tmp/unix')));
$socket->write(Str::of('woop woop!'));
$socket->close();

<?php
declare(strict_types = 1);

require 'vendor/autoload.php';

use Innmind\Socket\{
    Client\Unix,
    Address\Unix as Address
};
use Innmind\Immutable\Str;

$socket = new Unix(new Address('/tmp/unix'));
$socket->write(Str::of('woop woop!'));
$socket->close();

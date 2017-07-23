<?php
declare(strict_types = 1);

require 'vendor/autoload.php';

use Innmind\Socket\{
    Client\Internet,
    Internet\Transport
};
use Innmind\Url\Url;
use Innmind\Immutable\Str;

$socket = new Internet(
    Transport::tcp(),
    Url::fromString('//127.0.0.1:1234')->authority()
);
$socket->write(new Str('woop woop!'));
$socket->close();

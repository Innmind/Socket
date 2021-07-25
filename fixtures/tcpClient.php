<?php
declare(strict_types = 1);

require 'vendor/autoload.php';

use Innmind\Socket\{
    Client\Internet,
    Internet\Transport,
};
use Innmind\Url\Url;
use Innmind\Immutable\Str;

Internet::of(
    Transport::tcp(),
    Url::of('//127.0.0.1:1234')->authority()
)
    ->map(static function($socket) {
        $socket->write(Str::of('woop woop!'));
        $socket->close();

        return $socket;
    });

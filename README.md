# Socket

[![Build Status](https://github.com/innmind/socket/workflows/CI/badge.svg?branch=master)](https://github.com/innmind/socket/actions?query=workflow%3ACI)
[![codecov](https://codecov.io/gh/innmind/socket/branch/develop/graph/badge.svg)](https://codecov.io/gh/innmind/socket)
[![Type Coverage](https://shepherd.dev/github/innmind/socket/coverage.svg)](https://shepherd.dev/github/innmind/socket)

Layer on top of [`innmind/stream`](https://github.com/Innmind/Stream) to specifically work with sockets.

## Installation

```sh
composer require innmind/socket
```

## Usage

### Unix socket

Server example:

```php
use Innmind\Socket\{
    Server\Unix,
    Address\Unix as Address,
};
use Innmind\TimeContinuum\Earth\ElapsedPeriod;
use Innmind\Stream\Watch\Select;

$server = Unix::recoverable(Address::of('/tmp/my-socket'))->match(
    static fn($server) => $server,
    static fn() => throw new \RuntimeException('Unable to create socket'),
);
$select = Select::timeoutAfter(new ElapsedPeriod(100))
    ->forRead($server);

do {
    $select()
        ->flatMap(fn($ready) => $ready->toRead()->find(fn($stream) => $stream === $server))
        ->flatMap(fn($server) => $server->accept())
        ->match(
            static fn($incomingConnection) => $doSomething($incomingConnection),
            static fn() => null, // no incoming connection within the last 100 milliseconds
        )
} while (true);
```

The example above creates a socket at `/tmp/my-socket.sock` and will wait indefinitely. It will call `$doSomething()` with an incoming connection as soon as one is available.

Client example:

```php
use Innmind\Socket\{
    Client\Unix,
    Address\Unix as Address,
};

$client = Unix::of(Address::of('/tmp/my-socket'))->match(
    static fn($client) => $client,
    static fn() => throw new \RuntimeException('Unable to connect to socket'),
);
$client->write(Str::of('hello there!'))->match(
    static fn($client) => $continueToDoSomething($client),
    static fn($error) => null, // do something else when it failed to write to the socket
);
```

This will simply connect to the socket server declared above and will send the data `hello there!`.

If you want to read what the server send back you should use the stream [`Select`](https://github.com/Innmind/Stream#usage) in order to wait for the data to arrive.

### Internet socket

Same logic as the unix socket except the way you build the server:

```php
use Innmind\Socket\{
    Server\Internet,
    Internet\Transport,
};
use Innmind\IP\IPv4;
use Innmind\Url\Authority\Port;

$server = Internet::of(
    Transport::tcp(),
    IPv4::of('127.0.0.1'),
    Port::of(80),
);
//this will listen for incoming tcp connection on the port 80
```

and the client:

```php
use Innmind\Socket\{
    Client\Internet,
    Internet\Transport,
};
use Innmind\Url\Url;

$client = Internet::of(
    Transport::tcp(),
    Url::of('//127.0.0.1:80')->authority(),
);
//this will connect to a local socket on port 80
```

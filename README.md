# Socket

| `develop` |
|-----------|
| [![codecov](https://codecov.io/gh/Innmind/Socket/branch/develop/graph/badge.svg)](https://codecov.io/gh/Innmind/Socket) |
| [![Build Status](https://github.com/Innmind/Socket/workflows/CI/badge.svg)](https://github.com/Innmind/Socket/actions?query=workflow%3ACI) |

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
    Serve,
    Address\Unix as Address
};
use Innmind\Stream\Watch;
use Innmind\EventBus\EventBus;
use Innmind\TimeContinuum\ElapsedPeriod;

$server = Unix::recoverable(new Address('/tmp/my-socket'));
$serve = new Serve(
    new EventBus(/* see library for documentation */),
    /* an instance of Watch */
);
$serve($server);
```

The example above creates a socket at `/tmp/my-socket.sock` and will wait indefinitely. The loop will dispatch those events as soon as the data arrive:

* [`ConnectionReceived`](src/Event/ConnectionReceived.php)
* [`ConnectionClosed`](src/Event/ConnectionClosed.php)
* [`ConnectionReady`](src/Event/ConnectionReady.php)

Client example:

```php
use Innmind\Socket\{
    Client\Unix,
    Address\Unix as Address
};

$client = new Client(new Address('/tmp/my-socket'));
$client->write(new Str('hello there!'));
```

This will simply connect to the socket server declared above and will send the data `hello there!`.

If you want to read what the server send back you should use the stream [`Select`](https://github.com/Innmind/Stream#usage) in order to wait for the data to arrive.

### Internet socket

Same logic as the unix socket except the way you build the server:

```php
use Innmind\Socket\{
    Server\Internet,
    Internet\Transport
};
use Innmind\IP\IPv4;
use Innmind\Url\Authority\Port;

$server = new Internet(
    Transport::tcp(),
    new IPv4('127.0.0.1'),
    new Port(80)
);
//this will listen for incoming tcp connection on the port 80
```

and the client:

```php
use Innmind\Socket\{
    Client\Internet,
    Internet\Transport
};
use Innmind\Url\Url;

$client = new Client(
    Transport::tcp(),
    Url::fromString('//127.0.0.1:80')->authority()
);
//this will connect to a local socket on port 80
```

### Loop lifetime

By default the [`Loop`](src/Loop.php) use the [`Infinite`](src/Loop/Strategy/Infinite.php) strategy but you can easily build your own.

Let's say you don't want your loop to run more than an hour, you need to create a strategy like this:

```php
use Innmind\Socket\Loop\Strategy;
use Innmind\TimeContinuum\{
    TimeContinuumInterface,
    ElapsedPeriod
};

final class RunForAnHour implements Strategy
{
    private $start;
    private $clock;
    private $threshold;

    public function __construct(TimeContinuumInterface $clock)
    {
        $this->start = $clock->now();
        $this->clock = $clock;
        $this->threshold = new ElapsedPeriod(
            60 * 60 * 1000 //an hour
        );
    }

    public function __invoke(): bool
    {
        return $this->threshold->longerThan(
            $this
                ->clock
                ->now()
                ->elapsedSince($this->start)
        );
    }
}
```

Then build your loop with your strategy:

```php
use Innmind\TimeContinuum\TimeContinuum\Earth;

$loop = new Serve(
    new EventBus(/**/),
    /* instance of Watch */,
    new RunForAnHour(new Earth)
);
```

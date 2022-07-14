# Changelog

## [Unreleased]

### Fixed

- `Innmind\Socket\Server` not usable with `Innmind\Stream\Watch` as it was not `Readable`

## 4.0.0 - 2022-02-22

### Changed

- Update to `innmind/stream` `~3.0` (that replace exceptions by monads)
- `Innmind\Socket\Address\Unix` is now immutable and no longer checks if the directory exists
- `Innmind\Socket\Client\Internet` constructor is now private, use `::of()` named constructor instead
- `Innmind\Socket\Client\Internet::closed()` no longer close the socket when the server cut the connection
- `Innmind\Socket\Client\Internet::toString()` no longer return the address of the socket
- `Innmind\Socket\Client\Unix` constructor is now private, use `::of()` named constructor instead
- `Innmind\Socket\Client\Unix::closed()` no longer close the socket when the server cut the connection
- `Innmind\Socket\Client\Unix::toString()` no longer return the address of the socket
- `Innmind\Socket\Server::accept()` now returns a `Innmind\Immutable\Maybe<Innmind\Socket\Server\Connection>` instead of throwing an exception
- `Innmind\Socket\Server\Connection\Stream` constructor is now private, use `::of()` named constructor instead
- `Innmind\Socket\Server\Connection\Stream::toString()` no longer return the address of the socket
- `Innmind\Socket\Server\Internet` constructor is now private, use `::of()` named constructor instead
- `Innmind\Socket\Server\Unix` constructor is now private, use `::of()` named constructor instead
- `Innmind\Socket\Server\Unix::recoverable` now returns a `Innmind\Immutable\Maybe<Innmind\Socket\Server\Unix>` instead of throwing an exception

### Removed

- Support for php `7.4` and `8.0`
- `Innmind\Socket\Event\ConnectionClosed`
- `Innmind\Socket\Event\ConnectionReady`
- `Innmind\Socket\Event\ConnectionReceived`
- `Innmind\Socket\Event\Connection`
- `Innmind\Socket\Exception\DirectoryNotFound`
- `Innmind\Socket\Exception\FailedAcceptingIncomingConnection`
- `Innmind\Socket\Exception\FailedToOpenSocket`
- `Innmind\Socket\Exception\LogicException`
- `Innmind\Socket\Exception\SocketNotSeekable`
- `Innmind\Socket\Loop\Strategy`
- `Innmind\Socket\Loop\Strategy\Infinite`
- `Innmind\Socket\Loop\Strategy\Iterations`
- `Innmind\Socket\Serve`

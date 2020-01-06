<?php
declare(strict_types = 1);

namespace Innmind\Socket;

use Innmind\Socket\{
    Server\Connection,
    Event\ConnectionReceived,
    Event\ConnectionClosed,
    Event\ConnectionReady,
    Loop\Strategy,
    Loop\Strategy\Infinite
};
use Innmind\Stream\Watch;
use Innmind\EventBus\EventBus;

final class Serve
{
    private EventBus $dispatch;
    private Watch $watch;
    private Strategy $strategy;

    public function __construct(
        EventBus $dispatch,
        Watch $watch,
        Strategy $strategy = null
    ) {
        $this->dispatch = $dispatch;
        $this->watch = $watch;
        $this->strategy = $strategy ?? new Infinite;
    }

    public function __invoke(Server $server): void
    {
        $watch = $this->watch->forRead($server);

        do {
            $ready = $watch();

            try {
                if ($ready->toRead()->contains($server)) {
                    $connection = $server->accept();
                    $watch = $watch->forRead($connection);
                    ($this->dispatch)(new ConnectionReceived($connection));
                }

                $watch = $ready
                    ->toRead()
                    ->remove($server)
                    ->reduce(
                        $watch,
                        function(Watch $watch, Connection $connection): Watch {
                            if ($connection->closed()) {
                                $watch = $watch->unwatch($connection);
                                ($this->dispatch)(new ConnectionClosed($connection));

                                return $watch;
                            }

                            ($this->dispatch)(new ConnectionReady($connection));

                            return $watch;
                        }
                    );
            } catch (\Throwable $e) {
                ($this->dispatch)($e);
            }

        } while (($this->strategy)());
    }
}

<?php
declare(strict_types = 1);

namespace Innmind\Socket;

use Innmind\Socket\{
    Server\Connection,
    Event\ConnectionReceived,
    Event\ConnectionClosed,
    Event\DataReceived,
    Loop\Strategy,
    Loop\Strategy\Infinite
};
use Innmind\Stream\Select;
use Innmind\EventBus\EventBusInterface;
use Innmind\TimeContinuum\ElapsedPeriod;

final class Loop
{
    private $bus;
    private $timeout;
    private $strategy;

    public function __construct(
        EventBusInterface $bus,
        ElapsedPeriod $timeout,
        Strategy $strategy = null
    ) {
        $this->bus = $bus;
        $this->timeout = $timeout;
        $this->strategy = $strategy ?? new Infinite;
    }

    public function __invoke(Server $server): void
    {
        $select = (new Select($this->timeout))->forRead($server);

        do {
            $sockets = $select();

            try {
                if ($sockets->get('read')->contains($server)) {
                    $connection = $server->accept();
                    $select = $select->forRead($connection);
                    $this->bus->dispatch(new ConnectionReceived($connection));
                }

                $sockets
                    ->get('read')
                    ->remove($server)
                    ->foreach(function(Connection $connection) use (&$select) {
                        $text = $connection->read();

                        if ($text->length() === 0) {
                            $select = $select->unwatch($connection);
                            $this->bus->dispatch(new ConnectionClosed(
                                $connection->close()
                            ));

                            return;
                        }

                        $this->bus->dispatch(new DataReceived(
                            $connection,
                            $text
                        ));
                    });
            } catch (\Throwable $e) {
                $this->bus->dispatch($e);
            }

        } while (($this->strategy)());
    }
}

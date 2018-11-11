<?php
declare(strict_types = 1);

namespace Tests\Innmind\Socket;

use Innmind\Socket\{
    Loop,
    Loop\Strategy,
    Server\Unix,
    Server\Connection,
    Event\ConnectionReceived,
    Event\ConnectionClosed,
    Event\DataReceived,
    Address\Unix as Address
};
use Innmind\EventBus\EventBus;
use Innmind\TimeContinuum\ElapsedPeriod;
use Innmind\Server\Control\{
    ServerFactory,
    Server\Command
};
use PHPUnit\Framework\TestCase;

class LoopTest extends TestCase
{
    public function testInvokation()
    {
        $loop = new Loop(
            $bus = $this->createMock(EventBus::class),
            new ElapsedPeriod(0),
            new class implements Strategy {
                private $clientCalled = false;
                private $i = 0;

                public function __invoke(): bool
                {
                    if ($this->clientCalled === false) {
                        (new ServerFactory)
                            ->make()
                            ->processes()
                            ->execute(
                                Command::foreground('php')
                                    ->withArgument('fixtures/unixClient.php')
                            )
                            ->wait();

                        $this->clientCalled = true;
                    }

                    return $this->i++ < 3;
                }
            }
        );

        $bus
            ->expects($this->at(0))
            ->method('__invoke')
            ->with($this->callback(static function(ConnectionReceived $event): bool {
                return $event->connection() instanceof Connection;
            }));
        $bus
            ->expects($this->at(1))
            ->method('__invoke')
            ->with($this->callback(static function(DataReceived $event): bool {
                return $event->source() instanceof Connection &&
                    (string) $event->data() === 'woop woop!';
            }));
        $bus
            ->expects($this->at(2))
            ->method('__invoke')
            ->with($this->callback(static function(ConnectionClosed $event): bool {
                return $event->connection()->closed();
            }));

        $loop(Unix::recoverable(new Address('/tmp/unix')));
    }
}

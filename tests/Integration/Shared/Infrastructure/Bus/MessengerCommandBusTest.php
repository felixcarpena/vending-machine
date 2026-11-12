<?php

declare(strict_types=1);

namespace Tests\Integration\Shared\Infrastructure\Bus;

use DomainException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shared\Application\Bus\Command;
use Shared\Infrastructure\Bus\MessengerCommandBus;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

final class MessengerCommandBusTest extends TestCase
{
    #[Test]
    public function delivers_the_command_to_its_handler(): void
    {
        $command = new class implements Command {};
        $receiver = new class {
            public private(set) ?Command $received = null;

            public function __invoke(Command $command): void
            {
                $this->received = $command;
            }
        };
        $commandBus = self::commandBusWhere($command, $receiver);

        $commandBus->dispatch($command);

        self::assertSame($command, $receiver->received);
    }

    #[Test]
    public function lets_the_handler_exception_through_unwrapped(): void
    {
        $command = new class implements Command {};
        $refuser = new class {
            public function __invoke(Command $command): never
            {
                throw new DomainException('refused');
            }
        };
        $commandBus = self::commandBusWhere($command, $refuser);

        $this->expectExceptionObject(new DomainException('refused'));

        $commandBus->dispatch($command);
    }

    private static function commandBusWhere(Command $command, callable $isHandledBy): MessengerCommandBus
    {
        $locator = new HandlersLocator([$command::class => [$isHandledBy]]);

        return new MessengerCommandBus(new MessageBus([new HandleMessageMiddleware($locator)]));
    }
}
